<?php


namespace MaDnh\LaravelUpload;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

abstract class FileValidate extends FileValidateAbstract
{

    /**
     * @return FileException|\Exception|string
     */
    public function exception()
    {
        return new FileException(sprintf('Validate uploaded file failed at %s', class_basename(static::class)));
    }
}