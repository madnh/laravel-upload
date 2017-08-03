<?php


namespace MaDnh\LaravelUpload\Validate;


use MaDnh\LaravelUpload\FileValidate;

class ValidateFileName extends FileValidate
{

    public function validate($file)
    {
        $max_filename_length = array_get($this->profile, 'max_filename_length', 0);

        if ($max_filename_length === 0) {
            return true;
        }

        return (bool)((mb_strlen($file->getClientOriginalName(), "UTF-8") > $max_filename_length) ? false : true);
    }

    public function exception()
    {
        return 'Uploaded file has invalid file name';
    }
}