<?php


namespace MaDnh\LaravelUpload;


use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

abstract class FileValidateAbstract
{
    /**
     * @var array
     */
    public $profile;

    /**
     * ValidateGroup constructor.
     * @param array $profile
     */
    public function __construct($profile = [])
    {
        $this->profile = $profile;
    }

    /**
     * @param UploadedFile|\File|SymfonyUploadedFile|string $file
     * @return bool|\Exception|FileException
     */
    abstract public function validate($file);
}