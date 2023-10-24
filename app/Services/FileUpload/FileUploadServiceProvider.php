<?php

namespace App\Services\FileUpload;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\ServiceProvider;

class FileUploadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        FilesystemAdapter::macro('putFileAs', function ($path, $file, $name = null, $options = []) {
            if (is_null($name) || is_array($name)) {
                [$path, $file, $name, $options] = ['', $path, $file, $name ?? []];
            }

            if (is_resource($file)) {
                $stream = $file;
            } else if ($file instanceof UploadedFile) {
                if ($file->isFromBase64String()) {
                    $stream = fopen('php://temp','r+');
                    fwrite($stream, $file->getBase64String());
                    rewind($stream);
                } else {
                    $stream = fopen($file->getRealPath(), 'r');
                }
            } else {
                $stream = fopen(is_string($file) ? $file : $file->getRealPath(), 'r');
            }

            $result = $this->put(
                $path = trim($path.'/'.$name, '/'), $stream, $options
            );

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $result ? $path : false;
        });
    }

    public function register()
    {
        //
    }
}
