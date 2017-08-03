<?php


namespace MaDnh\LaravelUpload;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ValidateGroup extends FileValidateAbstract
{
    public $validates = [];


    /**
     * @param \File|\Illuminate\Http\UploadedFile|string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     * @return bool|\Exception|FileException
     * @throws \Exception
     */
    public function validate($file)
    {
        foreach ($this->validates as $validate) {
            if (!is_a($validate, FileValidateAbstract::class, true)) {
                throw new \Exception(sprintf('Upload file validate class must be instance of %s, %s given', class_basename(FileValidateAbstract::class), $validate));
            }

            /**
             * @var FileValidate|self $validator
             */
            $validator = new $validate($this->profile);

            try {
                $valid = $validator->validate($file);

                if (false === $valid) {
                    throw $this->getException($validator);
                }
            } catch (\Exception $e) {
                return $e;
            }
        }

        return true;
    }

    /**
     * @param FileValidate|self $validator
     * @return \Exception|FileException
     */
    protected function getException($validator)
    {
        $exception = $validator->exception();

        if (is_string($exception)) {
            $exception = new FileException($exception);
        } else if (!is_a($exception, \Exception::class)) {
            $exception = new \Exception('Validate exception must be string or an instance of \Exception, ' . gettype($exception) . ' given');
        }
        return $exception;
    }
}