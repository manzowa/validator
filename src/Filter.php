<?php

namespace Manzowa\Validator;

use \Manzowa\Validator\Utils\UploadedFile;
use \Manzowa\Validator\Type\FileType;

/**
 * Trait Filter
 * 
 * PHP version 8.0.0
 * 
 * @category Manzowa\Validator
 * @package  Manzowa\Validator
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://manzowa.com
 */
trait Filter
{
    /**
     * Check if the value is empty and add an error if so.
     *
     * @return self
     */
    public function isEmpty(): self
    {
        if (empty($this->getValue()) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('empty'));
        }
        return $this;
    }
    /**
     * Check if the value is empty and add an error if so.
     *
     * @return self
     */
    public function isRequired(): self
    {
        if (empty($this->getValue()) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('empty'));
        }
        return $this;
    }
    /**
     * file function.
     *
     * @return Manzowa\Validator\Type\FileType
     */
    public function file(
        int $maxSizeFile = 1048576,  
        array $mimes = []
    ) : FileType {
        $filetype = new FileType($maxSizeFile, $mimes);
        $filetype->setValidator($this);
        return $filetype;
    }

    /**
     * Check if the value is a valid number and add an error if not.
     *
     * @return self
     */
    public function isNumber(): self
    {
        if (!is_numeric($this->getValue()) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('number'));
        }
        return $this;
    }

    /**
     * Check if the value's size matches the specified size.
     *
     * @param int $digit Expected size of the value
     *
     * @return self
     */
    public function size(int $digit = 8): self
    {
        $size = (int) mb_strlen((string) $this->getValue());
        if ($size !== $digit && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('size'));
        }
        return $this;
    }

    /**
     * Check if the value matches the specified field.
     *
     * @param string $name Field to compare with
     *
     * @return self
     */
    public function same(string $name = ""): self
    {
        $tampon = $this->getTampon($name);
        if (strcmp($this->getValue(), trim($tampon)) !== 0 
            && $this->isNotAlreadyError()
        ) {
            $this->addError($this->getField(), $this->getMessage('confirm'));
        }
        return $this;
    }

    /**
     * Check if the value is a valid email.
     *
     * @return self
     */
    public function isEmail(): self
    {
        if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)
             && $this->isNotAlreadyError()
        ) {
            $this->addError($this->getField(), $this->getMessage('invalid'));
        }
        return $this;
    }

    /**
     * Validate isFile upload.
     * @deprecated version v1.0.5
     * 
     * @param int $max_size_file -
     * @param array $mimes
     * 
     *
     * @return self
     */
    public function isFiles(
        int $max_size_file = 1048576, 
        array $mimes = []
    ): self {
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
                    if ($uploadedFiled->getSize() > $max_size_file 
                        && !$this->hasError("max_file_size")
                    ) {
                        $this->addError(
                            "max_file_size",
                            $this->getMessage("maxSizeFile", $max_size_file)
                        );
                        continue;
                    }
                    // Check file MIME type
                    $mime = $uploadedFiled->getMimeTypeFromFile();
                    if (count($mimes) > 0 && !in_array($mime, $mimes)) {
                        $this->addError(
                            "invalid_file_type",
                            $this->getMessage("invalidFileType")
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
                if ($uploadedFiled->getSize() > $max_size_file 
                    && !$this->hasError("max_file_size")
                ) {
                    $this->addError(
                        "max_file_size",
                        $this->getMessage("maxSizeFile", $max_size_file)
                    );
                    continue;
                }
                // Check file MIME type
                $mime = $uploadedFiled->getMimeTypeFromFile();
                if (count($mimes) > 0 && !in_array($mime, $mimes)) {
                    $this->addError(
                        "invalid_file_type",
                        $this->getMessage("invalidFileType")
                    );
                    continue;
                }
                $rowFile[] = $uploadedFiled;
            }
        }  

        
        $this->inputs[$this->getField()] = $rowFile;
        return $this;
    }
    
    /**
     * Replace placeholders in the error message with actual values.
     *
     * @param string $subject The error message with placeholders
     * @param string $replace The value to replace placeholders with
     * @param string $pattern The pattern to search for placeholders
     *
     * @return string
     */
    public function match(
        string $subject, 
        string $replace, 
        string $pattern = '/{{input}}/i'
    ): string {
        if (preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches as $match) {
                $subject = str_replace($match[0], $replace, $subject);
            }
        }
        return $subject;
    }
}
