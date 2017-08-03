<?php

namespace MaDnh\LaravelUpload;

use File;
use Illuminate\Http\UploadedFile;

abstract class UploadHandler
{
    /**
     * @var string|array string is config profile name
     */
    public $profile;

    /**
     * Store path, override store path in profile
     * @var string
     */
    public $store_path;

    /**
     * @var FileValidateAbstract[]
     */
    public $validates = [
        \MaDnh\LaravelUpload\Validate\ValidateUploadedFile::class,
        \MaDnh\LaravelUpload\Validate\ValidateFileName::class,
        \MaDnh\LaravelUpload\Validate\ValidateFileType::class,
        \MaDnh\LaravelUpload\Validate\ValidateFileSize::class
    ];

    /**
     * @var null|true|string
     */
    protected $lastValidate;

    /**
     * @param string $profile
     * @throws \Exception
     */
    public function setProfile($profile)
    {
        if (is_string($profile)) {
            $profile = config('upload.profiles.' . $profile);

            if (empty($profile)) {
                throw new \Exception('Upload file profile not found');
            }
        }

        $profile = array_merge([
            'file_types' => true,
            'size' => true
        ], (array)$profile);

        $this->profile = $profile;
    }


    protected function makeSourceProfileLoaded()
    {
        if (!is_array($this->profile)) {
            $this->setProfile(!empty($this->profile) ? $this->profile : config('upload.default_profile'));
        }
    }


    /**
     * @param $file
     * @return true
     * @throws \Exception
     */
    public function validate($file)
    {
        $this->makeSourceProfileLoaded();
        $validator = new ValidateGroup($this->profile);
        $validator->validates = $this->validates;
        $validate_result = $validator->validate($file);

        if (true === $validate_result) {
            return true;
        }

        if (is_a($validate_result, \Exception::class)) {
            throw $validate_result;
        }

        throw new \Exception('The uploaded file is invalid');
    }

    /**
     * Validate file silent
     * @param $file
     * @return bool
     */
    public function isValidate($file)
    {
        try {
            if ($this->validate($file)) {
                return true;
            }
        } catch (\Exception $e) {
            //
        }

        return false;
    }

    /**
     * @param UploadedFile|File|string $file
     * @return UploadedFile
     */
    public function handle($file)
    {
        $this->makeSourceProfileLoaded();
        $this->validate($file);
        $this->process($file);

        return $file;
    }

    abstract public function process($file);

    protected function getStorePath()
    {
        return $this->store_path ? $this->store_path : $this->profile['store_path'];
    }
}