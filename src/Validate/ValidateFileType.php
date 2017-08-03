<?php


namespace MaDnh\LaravelUpload\Validate;


use MaDnh\LaravelUpload\FileValidate;

class ValidateFileType extends FileValidate
{
    public function validate($file)
    {
        $file_ext = strtolower($file->getClientOriginalExtension());
        $uploadable_filetypes = config('file_types.uploadable', []);
        $ignore_files = array_merge(config('file_types.ignore', []), config('file_types.blocked', []));

        return !in_array($file_ext, $ignore_files)
            && in_array($file_ext, $uploadable_filetypes)
            && ($this->profile['file_types'] === true || in_array($file_ext, (array)$this->profile['file_types']));
    }

    public function exception()
    {
        return 'Uploaded file type is not valid';
    }
}