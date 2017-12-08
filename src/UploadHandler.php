<?php

namespace MaDnh\LaravelUpload;

use File;
use Illuminate\Http\UploadedFile;

abstract class UploadHandler
{
    /**
     * @var array
     */
    protected $profile;

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
     * @param array $profile
     */
    public function setProfile(array $profile)
    {
        $profile = array_merge($this->getDefaultProfileConfig(), (array)$profile);

        $this->profile = $profile;
        }

    /**
     * @return array
     */
    protected function getDefaultProfileConfig()
    {
        return [
            'store_path' => storage_path('/app'),
            'file_types' => true,
            'size' => true
        ];

    }

    /**
     * @throws \Exception
     */
    protected function makeSourceProfileLoaded()
    {
        if (!is_array($this->profile)) {
            $this->setProfile($this->getProfile());
        }
    }

    /**
     * @return array
     */
    protected function getProfile()
    {
        return [];
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
     *
     * @return UploadedFile
     * @throws \Exception
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