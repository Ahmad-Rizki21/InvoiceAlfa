<?php

declare(strict_types=1);

namespace App\Services\FileUpload;

use Illuminate\Http\File as LaravelFile;
use Illuminate\Http\UploadedFile as LaravelUploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use SplFileInfo;
use Symfony\Component\Mime\Part\File as SymfonyMimeFile;

class UploadedFileFactory
{
    /**
     * The uploaded file.
     *
     * @var \Illuminate\Http\UploadedFile
     */
    protected $uploadedFile;

    public function __construct($uploadedFile)
    {
        if ($uploadedFile instanceof UploadedFile) {
            $this->uploadedFile = $uploadedFile;
        } else if ($uploadedFile instanceof SymfonyUploadedFile) {
            $this->uploadedFile = UploadedFile::createFromBase($uploadedFile);
        } else if ($uploadedFile instanceof SymfonyFile || $uploadedFile instanceof LaravelFile) {
            $this->uploadedFile = UploadedFile::createFromBase(new SymfonyUploadedFile(
                $uploadedFile->getRealPath(),
                $uploadedFile->getBasename(),
                $uploadedFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        } else if ($uploadedFile instanceof SymfonyMimeFile) {
            $symfonyFile = new SymfonyFile($uploadedFile->getPath(), true);
            $this->uploadedFile = UploadedFile::createFromBase(new SymfonyUploadedFile(
                $symfonyFile->getRealPath(),
                $symfonyFile->getBasename(),
                $symfonyFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        } else if ($uploadedFile instanceof SplFileInfo) {
            $symfonyFile = new SymfonyFile($uploadedFile->getRealPath(), true);
            $this->uploadedFile = UploadedFile::createFromBase(new SymfonyUploadedFile(
                $symfonyFile->getRealPath(),
                $symfonyFile->getBasename(),
                $symfonyFile->getMimeType(),
                \UPLOAD_ERR_OK
            ));
        } else if (is_string($uploadedFile)) {

        }
    }
}


