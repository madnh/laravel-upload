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
     * @var UploadedFile
     */
    protected $file;

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

    protected function makeSourceProfileLoaded()
    {
        if ( ! is_array($this->profile)) {
            $this->setProfile($this->getProfile());
        }
    }

    /**
     * @return array
     */
    protected abstract function getProfile();

    /**
     * @param UploadedFile $file
     *
     * @return true
     * @throws \Exception
     */
    public function validate($file = null)
    {
        $this->makeSourceProfileLoaded();

        if(!$file){
            $file = $this->file;
        }

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
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function isValidate($file = null)
    {
        if(!$file){
            $file = $this->file;
        }

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
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param UploadedFile $file
     *
     * @return UploadHandler
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        return $this;
    }


    /**
     * @param UploadedFile|File|string $file
     *
     * @return UploadedFile
     * @throws \Exception
     */
    public function handle($file = null)
    {
        $this->makeSourceProfileLoaded();

        if($file){
            $this->setFile($file);
        }

        $this->validate();
        $this->process();

        return $file;
    }


    abstract public function process();
}