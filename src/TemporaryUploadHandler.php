<?php

namespace MaDnh\LaravelUpload;

use File;
use Illuminate\Http\UploadedFile;
use MaDnh\LaravelUpload\Exceptions\HandleEmptyTemporaryUploadedFile;
use MaDnh\LaravelUpload\Exceptions\TemporaryUploadedFileNotFound;

abstract class TemporaryUploadHandler extends UploadHandler
{
    /**
     * @var array $temp_stored_info
     */
    protected $temp_stored_info = [];

    /**
     * @return UploadedFile
     */
    public function process()
    {
        $this->storeFileTemporary();
        $this->processTemporaryFile();

        return $this->file;
    }

    protected function storeFileTemporary()
    {
        $store_path = $this->getTempStorePath();
        $file_name = $this->getTempFilename($this->file);
        $file_name_prefix = $this->getTempFileNamePrefix();
        $temp_filename = $file_name_prefix . '__' . $file_name;

        $this->file = $this->file->move($store_path, $temp_filename);

        $this->temp_stored_info = [
            'file' => $this->file,
            'filename' => $file_name,
            'temp_path' => $store_path,
            'temp_filename' => $temp_filename,
            'temp_file_path' => $store_path . DIRECTORY_SEPARATOR . $temp_filename
        ];
    }

    /**
     * Get name (include file's extension) of temporary file
     *
     * @param UploadedFile|File $file
     *
     * @return null|string
     */
    protected function getTempFilename($file)
    {
        switch ($this->profile['temporary_name']) {
            case 'hash':
                return $file->hashName();
                break;
            case 'random':
                return str_random(16) . '.' . $file->guessExtension();
                break;
            default:
                return $this->sanitizeFileName($file->getClientOriginalName());
        }
    }

    /**
     * @return string
     */
    protected function getTempFileNamePrefix()
    {
        return 'temp_' . time() . '_' . str_random(7);
    }

    /**
     * @param string $filename
     * @param string $store_path
     *
     * @return string
     */
    public function getTemporaryFilePath($filename, $store_path = null)
    {
        $store_path = $store_path ? $store_path : static::getTempStorePath();

        return $store_path . DIRECTORY_SEPARATOR . $filename;
    }

    public function getTempStorePath()
    {
        return config('upload.upload_temp_path', storage_path('app/upload_temp'));
    }

    /**
     * @param $filename
     *
     * @return bool
     */
    public function temporaryFileExists($filename)
    {
        $temp_store_path = $this->getTemporaryFilePath($filename);

        return File::exists($temp_store_path);
    }

    /**
     * Get real filename from temporary filename
     *
     * @param string $temp_filename
     *
     * @return string
     */
    protected function getRealName($temp_filename)
    {
        return $this->sanitizeFileName(last(explode('__', $temp_filename, 2)));
    }

    /**
     * @param null $path
     *
     * @return array
     */
    public function getTempStoredInfo($path = null)
    {
        return $path ? array_get($this->temp_stored_info, $path) : $this->temp_stored_info;
    }

    /**
     * Handle uploaded file
     * If this class support temporary upload then file in this method must be a file in storage, not uploading file.
     *
     * @param string $temp_filename
     * @param string $save_name Only file name, no extension
     *
     * @return string Saved filename (with extension)
     *
     * @throws HandleEmptyTemporaryUploadedFile
     * @throws TemporaryUploadedFileNotFound
     */
    public function handleTemporaryUploadedFile($temp_filename = null, $save_name = null)
    {
        $this->makeSourceProfileLoaded();

        if ($temp_filename) {
            $tempFilePath = static::getTemporaryFilePath($temp_filename);
        } else {
            if (empty($this->temp_stored_info)) {
                throw new HandleEmptyTemporaryUploadedFile('Temporary filename is required');
            }

            $temp_filename = $this->temp_stored_info['filename'];
            $tempFilePath = $this->temp_stored_info['file_path'];
        }

        if ( ! $this->temporaryFileExists($temp_filename)) {
            throw new TemporaryUploadedFileNotFound('Uploaded file not found');
        }

        $save_name = $save_name ?: $this->getRealName($tempFilePath);

        return $this->processUploadedFile($tempFilePath, $save_name);
    }

    /**
     * @param string $dangerousFilename
     * @param string $platform
     *
     * @return string
     */
    protected function sanitizeFileName($dangerousFilename, $platform = 'Unix')
    {
        if (in_array(strtolower($platform), array('unix', 'linux'))) {
            // our list of "dangerous characters", add/remove
            // characters if necessary
            $dangerousCharacters = array(" ", '"', "'", "&", "/", "\\", "?", "#");
        } else {
            // no OS matched? return the original filename then...
            return $dangerousFilename;
        }

        // every forbidden character is replace by an underscore
        return str_replace($dangerousCharacters, '_', $dangerousFilename);
    }

    /**
     * Process temporary after moved to temporary folder
     * File in this context is normal file, isn't uploading file.
     */
    protected abstract function processTemporaryFile();

    /**
     * @param string $tempFilePath
     * @param string $save_name
     *
     * @return string Saved filename (with extension)
     */
    abstract public function processUploadedFile($tempFilePath, $save_name);
}