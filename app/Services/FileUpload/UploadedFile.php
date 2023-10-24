<?php

declare(strict_types=1);

namespace App\Services\FileUpload;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Http\File as LaravelFile;
use Illuminate\Http\UploadedFile as LaravelUploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use Symfony\Component\Mime\Part\File as SymfonyMimeFile;

class UploadedFile extends LaravelUploadedFile
{
    /**
     * The original base64 string, if the uploaded file is from base64 string
     *
     * @var string
     */
    protected $base64String = '';

    /**
     * The disk uploaded file using.
     *
     * @var string
     */
    protected $disk = '';

    public static function from($uploadedFile): static
    {
        if ($uploadedFile instanceof static) {
            return $uploadedFile;
        }

        if ($uploadedFile instanceof LaravelUploadedFile || $uploadedFile instanceof SymfonyUploadedFile) {
            return static::createFromBase($uploadedFile);
        }

        if ($uploadedFile instanceof SymfonyFile || $uploadedFile instanceof LaravelFile) {
            return static::createFromBase(new SymfonyUploadedFile(
                $uploadedFile->getRealPath(),
                $uploadedFile->getBasename(),
                $uploadedFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        }

        if ($uploadedFile instanceof SymfonyMimeFile) {
            $symfonyFile = new SymfonyFile($uploadedFile->getPath(), true);
            return static::createFromBase(new SymfonyUploadedFile(
                $symfonyFile->getRealPath(),
                $symfonyFile->getBasename(),
                $symfonyFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        }

        if ($uploadedFile instanceof SplFileInfo) {
            $symfonyFile = new SymfonyFile($uploadedFile->getRealPath(), true);
            return static::createFromBase(new SymfonyUploadedFile(
                $symfonyFile->getRealPath(),
                $symfonyFile->getBasename(),
                $symfonyFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        }

        if (is_string($uploadedFile)) {
            $instance = new static('/tmp/fakepath', 'fakename', static::guessBase64StringMimeType($uploadedFile), \UPLOAD_ERR_OK);
            $instance->setBase64String($uploadedFile);

            return $instance;
        }

        throw new Exception('Could not instantiate UploadedFile');
    }

    public function setBase64String(string $content): void
    {
        $this->base64String = $content;
    }

    public function getBase64String(): string
    {
        if (empty($this->base64String)) {
            return base64_encode($this->get());
        }

        return $this->base64String;
    }

    public function isFromBase64String(): bool
    {
        return ! empty($this->base64String);
    }

    public static function guessBase64StringMimeType(string $base64String)
    {
        $finfo = finfo_open();

        return finfo_buffer($finfo, base64_decode($base64String), FILEINFO_MIME_TYPE);
    }

    /**
     * Store the uploaded file on a filesystem disk.
     *
     * @param  string  $path
     * @param  string|array  $name
     * @param  array|string  $options
     * @return string|false
     */
    public function storeAs($path, $name = null, $options = [])
    {
        if (is_null($name) || is_array($name)) {
            [$path, $name, $options] = ['', $path, $name ?? []];
        }

        $options = $this->parseOptions($options);

        $disk = Arr::pull($options, 'disk');

        if (! $disk && ! empty($this->disk)) {
            $disk = $this->disk;
        }

        return Container::getInstance()->make(FilesystemFactory::class)->disk($disk)->putFileAs(
            $path, $this, $name, $options
        );
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Get a filename for the file.
     *
     * @param  string|null  $path
     * @return string
     */
    public function hashName($path = null)
    {
        if ($path) {
            $path = rtrim($path, '/').'/';
        }

        $hash = $this->hashName ?: $this->hashName = (string) Str::ulid();

        if ($extension = $this->guessExtension()) {
            $extension = '.'.$extension;
        }

        return $path.$hash.$extension;
    }
}
