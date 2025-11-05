<?php

namespace App\Imports;

use App\Enums\ImportType;
use App\Models\ImportCache;
use App\Services\FileUpload\UploadedFile;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;

class ImportCacheImport implements ToCollection
{
    protected ImportType $importType;
    protected string $importPath;

    public function __construct(ImportType $importType, string $importPath)
    {
        $this->importType = $importType;
        $this->importPath = $importPath;
    }

    public static function uploadFile($uploadedFile)
    {
        static::removeOldFiles();

        $uploadedFile = UploadedFile::from($uploadedFile);

        return $uploadedFile->disk('local')->storeAs('import-cache', 'fimp' . time() . '---' . $uploadedFile->hashName());
    }

    public static function removeOldFiles()
    {
        $storage = static::getStorage();
        $files = $storage->allFiles('import-cache');

        foreach ($files as $file) {
            try {
                $timestamp = explode('---', $file);
                $timestamp = explode('fimp', $timestamp[0])[1];

                if (Carbon::now()->diffInDays(Carbon::createFromTimestamp((int) $timestamp))) {
                    $storage->delete($file);
                }
            } catch (Throwable $e) {
                throw $e;
            }
        }
    }

    public static function getUploadedFile($importPath)
    {
        return static::getStorage()->path($importPath);
    }

    public static function deleteUploadedFile($importPath)
    {
        return static::getStorage()->delete($importPath);
    }

    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $items = [];
        $now = Carbon::now();
        $model = new ImportCache();

        ImportCache::where('import_type', $this->importType->value)
                ->where('import_path', $this->importPath)
                ->where('created_at', '<=', Carbon::now()->startOfDay())->delete();

        foreach ($collection as $i => $row) {
            if ($i === 0) {
                continue;
            }

            $content = array_filter($row->all());

            if (empty($content)) {
                continue;
            }

            $items[] = [
                'id' => $model->newUniqueId(),
                'import_path' => $this->importPath,
                'import_type' => $this->importType->value,
                'content' => $row,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        ImportCache::insert($items);
    }

    protected static function getStorage()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('local');

        return $storage;
    }
}
