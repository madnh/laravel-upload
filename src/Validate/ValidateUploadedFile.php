<?php


namespace MaDnh\LaravelUpload\Validate;


use MaDnh\LaravelUpload\FileValidate;

class ValidateUploadedFile extends FileValidate
{
    public function validate($file)
    {
        return $file->isValid();
    }

    public function exception()
    {
        return 'Uploaded file is invalid';
    }
}