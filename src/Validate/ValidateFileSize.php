<?php


namespace MaDnh\LaravelUpload\Validate;


use MaDnh\LaravelUpload\FileValidate;

class ValidateFileSize extends FileValidate
{
    public function validate($file)
    {
        return true === $this->profile['size'] || $file->getSize() <= $this->profile['size'];
    }

    public function exception()
    {
        return 'The uploaded file exceeds the size limit';
    }


}