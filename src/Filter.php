<?php

namespace Manzowa\Validator;

/**
 * Class trait Filter
 * 
 * PHP version 8.0.0
 * 
 * @category Manzowa\Validator
 * @package  Manzowa\Validator
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://cshungu.fr
 */
trait Filter
{
    /**
     * Method isEmpty
     * 
     * It allows you to check if the field is empty.
     * 
     * @return self;
     */
    public function isEmpty(): self
    {
        if (empty($this->getValeur()) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('empty'));
        }
        return $this;
    }
    /**
     * Method isNumber
     * 
     * It is used to check if the type of the value entered is number
     * 
     * @return self;
     */
    public function isNumber(): self
    {
        if (!is_numeric($this->getValeur()) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('number'));
        }
        return $this;
    }

    /**
     * Method size
     * 
     * It allows you to check the size of the entered value.
     * 
     * @param int $digit - Nombre 
     * 
     * @return self;
     */
    public function size(int $digit = 8): self
    {
        $size = (int) mb_strlen((string) $this->getValeur());
        if (($size !== $digit) && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('size'));
        }
        return $this;
    }

    /**
     * Method same
     * 
     * It allows you to compare the value of two fields.
     * 
     * @param int $name -  
     * 
     * @return self;
     */
    public function same(string $name = ""): self
    {
        $tampon = $this->getTampon($name);
        if ((strcmp($this->getValeur(), trim($tampon)) !== 0)
            && ($this->isNotAlreadyError())
        ) {
            $this->addError($this->getField(), $this->getMessage('confirm'));
        }
        return $this;
    }

    /**
     * Method isEmail
     * 
     * It allows you to check if the e-mail address entered is valid.
     * 
     * @return self;
     */
    public function isEmail(): self
    {
        $email = filter_var($this->getValeur(), FILTER_VALIDATE_EMAIL);
        if (!$email && $this->isNotAlreadyError()) {
            $this->addError($this->getField(), $this->getMessage('invalid'));
        }
        return $this;
    }
    /**
     * Method isFile
     * 
     * @param int $max_size_file - 
     *
     * @return void
     */
    public function isFiles(int $max_size_file = 1048576)
    {
        $rows = [];
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $files) {
                $counter = count($files['name']);
                for ($i = 0; $i < $counter; $i++) {
                    if ($files['size'][$i] > $max_size_file && !$this->hasError("max_file_size")) {
                        $this->addError(
                            "max_file_size",
                            $this->getMessage("maxSizeFile", $max_size_file)
                        );
                        continue;
                    } else {
                        $file = [
                            "name"     => $files['name'][$i],
                            "type"     => $files['type'][$i],
                            "tmp_name" => $files['tmp_name'][$i],
                            "error"    => $files['error'][$i],
                            "size"     => $files['size'][$i],
                        ];
                    }
                    $rows[$key][] = $file;
                }
            }
            $this->resultats['files'] = $rows;
        }
    }
}
