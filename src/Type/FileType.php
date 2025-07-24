<?php

namespace Manzowa\Validator\Type;

use Manzowa\Validator\Validator;
use \Manzowa\Validator\Utils\UploadedFile;

class FileType
{
    protected Validator $validator;
    protected array $dataFiles = [];

    public function __construct(int $maxSizeFile = 1048576, array $mimes = []) {
        $this->_Files($maxSizeFile, $mimes);
    }
    public function setValidator(Validator $validator): self {
        $this->validator = $validator;
        return $this;
    }
    public function getValidator(): Validator {
        return $this->validator;
    }
    public function get()
    {
        // Insertion Resultats
        $this->getValidator()->setInput(
            $this->getValidator()->getField(),
            $this->dataFiles
        );
        return $this->getValidator()->get();
    }

    public function isRequired(): self 
    {
        if (!$this->_FileExist()) {
            $this->getValidator()->addError(
                $this->getValidator()->getField(), 
                $this->getValidator()->getMessage('empty')
            );
        }
        return $this;
    }

    private function _Files(int $maxSizeFile, array $mimes): self 
    {
        if (empty($_FILES)) {
            return $this;
        }
        $rowFile = [];
        foreach ($_FILES as $files) {
            $isMultiArray = is_array($files['name'])?? false;
            if ($isMultiArray) {
                $counter = count($files['name']);
                for ($i = 0; $i < $counter; $i++) {
                    $uploadedFiled = new UploadedFile(
                        $files['tmp_name'][$i],
                        $files['size'][$i],
                        $files['error'][$i],
                        $files['name'][$i],
                        $files['type'][$i]
                    );
                    // Check max file size
                    if ($uploadedFiled->getSize() > $maxSizeFile 
                        && !$this->getValidator()->hasError("max_file_size")
                    ) {
                        $this->getValidator()->addError(
                            "max_file_size",
                            $this->getValidator()->getMessage("maxSizeFile", $maxSizeFile)
                        );
                        continue;
                    }
                    // Check file MIME type
                    $mime = $uploadedFiled->getMimeTypeFromFile();
                    if (count($mimes) > 0 && !in_array($mime, $mimes)) {
                        $this->getValidator()->addError(
                            "invalid_file_type",
                            $this->getValidator()->getMessage("invalidFileType")
                        );
                        continue;
                    }
                    $rowFile[] = $uploadedFiled;
                }
            } else {
                $uploadedFiled = new UploadedFile(
                    $files['tmp_name'],
                    $files['size'],
                    $files['error'],
                    $files['name'],
                    $files['type']
                );
                // Check max file size
                if ($uploadedFiled->getSize() > $maxSizeFile 
                    && !$this->getValidator()->hasError("max_file_size")
                ) {
                    $this->getValidator()
                        ->addError(
                        "max_file_size",
                        $this->getValidator()
                            ->getMessage("maxSizeFile", $maxSizeFile)
                    );
                    continue;
                }
                // Check file MIME type
                $mime = $uploadedFiled->getMimeTypeFromFile();
                if (count($mimes) > 0 && !in_array($mime, $mimes)) {
                    $this->getValidator()->addError(
                        "invalid_file_type",
                        $this->getValidator()->getMessage("invalidFileType")
                    );
                    continue;
                }
                $rowFile[] = $uploadedFiled;
            }
        }
        $this->dataFiles = $rowFile;
    
        return $this;
    }

    private function _FileExist() 
    {
        $isBool = true;
        foreach ($this->dataFiles as $dataFile) {
            if (is_object($dataFile) && $dataFile instanceof UploadedFile) {
                if ($dataFile->getError() === 4) {
                    $isBool = false;
                }
            }
        }
        return $isBool;
    }
}