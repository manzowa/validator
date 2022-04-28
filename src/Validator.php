<?php

namespace Wenzawa\Validator;

use \Closure;
use \ArrayAccess;
use \Iterator;
use \Countable;

/**
 * Class Validator
 * 
 * PHP version 8.0.0
 * 
 * @category Wenzawa\Validator
 * @package  Wenzawa\Validator
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://cshungu.fr
 */
class Validator implements ArrayAccess, Iterator, Countable
{
    use Messages;
    use Filter;
    /**
     * Variable of  errors
     *
     * @var array
     */
    protected $errors    = [];
    /**
     * Variable of methods
     *
     * @var array
     */
    protected $methods   = [];
    protected $resultats = [];
    protected $filters   = [];
    protected $keys      = [];
    protected $inputs    = [];
    protected $field     = "";
    protected $position  = 0;
    protected $verbe     = "post";

    /**
     * Method __construct
     * 
     * @param object|null $objet - 
     *
     * @return void
     */
    public function __construct(protected $objet = null)
    {
        $this->position = 0;
        $this->keys     = array_keys($this->filters);
    }

    /**
     * Method filter
     * 
     * @param string $verbe -
     *
     * @return void
     */
    public function filter(string $verbe = "post"): self
    {
        $this->setVerbe($verbe);
        foreach ($this as $key => $callback) {
            $this->setField($this->key());
            if ($this->isClosure($callback)) {
                $method = "method" . ucfirst(trim($key));
                $this->bindMethod($method, $callback);
                $this->addInput($key);
                $this->{$method}();
            }
        }
        return $this;
    }

    /**
     * Method set
     * 
     * @param string $field = Nom Champs
     * 
     * @return mixed;
     */
    protected function setField($field): self
    {
        if ($field) {
            $this->field = $field;
        }
        return $this;
    }
    /**
     * Method addFilter
     * 
     * It allows to add the filter and retrieves the give.
     * 
     * via [POST, GET, etc...]
     * 
     * @param string $name -
     *
     * @return void
     */
    protected function addInput(string $name): self
    {
        if ($name) {
            $this->inputs[$name] = filter_input(
                $this->getVerbe(),
                $name
            );
        }
        return $this;
    }

    /**
     * Method getField
     * 
     * @return string;
     */
    protected function getField(): string
    {
        return $this->field;
    }

    /**
     * Method getValeur
     * 
     * @return mixed;
     */
    protected function getValeur()
    {
        return  $this->inputs[$this->getField()] ?? "";
    }

    /**
     * Method getTampon
     * 
     * It allows you to return the value of any field.
     * 
     * @param string $name - 
     * 
     * @return string;
     */
    protected function getTampon(?string $name = ""): string
    {
        $tampo = "";
        if (!empty($name)) {
            $tampo = $this->inputs[$name] ?? "";
        }
        return $tampo;
    }

    /**
     * Method getMessage
     * 
     * @param string $name - Get error message
     * 
     * @return mixed;
     */
    protected function getMessage(?string $name = "")
    {
        return  $this->messages[$name] ?? "";
    }
    /**
     * Method get
     * 
     * @return mixed;
     */
    protected function get(): self
    {
        if (count($this->errors) === 0) {
            $this->resultats[$this->getField()] = $this->getValeur();
        }
        return $this;
    }
    /**
     * Method failed
     *
     * @return boolean
     */
    public function failed(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Method resultats
     *
     * @return void
     */
    public function resultats(): array
    {
        $posts = filter_input_array($this->getVerbe());
        $resultDiff = array_diff_assoc($posts, $this->resultats);
        $donnees = filter_var_array(
            $resultDiff,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_HIGH
        );
        return array_merge($this->resultats, $donnees);
    }

    /**
     * Method errors
     *
     * @return void
     */
    public function errors(): array
    {
        return $this->errors;
    }
    /**
     * Method addError
     * 
     * @param string $name    -
     * @param string $message -
     *
     * @return void
     */
    protected function addError($name, $message): self
    {
        if ($name) {
            $this->errors[$name] = $this->match($message, $name);
        }
        return $this;
    }
    /**
     * Method hasErro
     * 
     * @param string $name -
     *
     * @return boolean
     */
    protected function hasError($name): bool
    {
        return array_key_exists($name, $this->errors);
    }
    /**
     * Method getVerbe
     *
     * @return string
     */
    public function getVerbe(): string
    {
        $resultats = "";
        try {
            $resultats = match ($this->verbe) {
                'post'   => INPUT_POST,
                'get'    => INPUT_GET,
                'cookie' => INPUT_COOKIE,
                'server' => INPUT_SERVER,
                'env'    => INPUT_ENV
            };
        } catch (\UnhandledMatchError $e) {
            throw new \Exception($e->getMessage());
        }
        return $resultats;
    }
    /**
     * Method getVerbe
     * 
     * @param string $verbe - 
     *
     * @return self
     */
    protected function setVerbe(string $verbe = "post"): self
    {
        if ($verbe) {
            $this->verbe = $verbe;
        }
        return $this;
    }
    /**
     * Method ready
     *
     * @return void
     */
    protected function isNotAlreadyError(): bool
    {
        return !$this->hasError($this->getField());
    }

    /**
     * Method bindMethod
     * 
     * It allows linked the external function to class
     *
     * @param string  $methodName - 
     * @param Closure $method     - 
     * 
     * @return mixed
     */
    public function bindMethod(string $methodName, Closure $method): self
    {
        if (!$this->isClosure($method)) {
            throw new \InvalidArgumentException(
                'Second param must be callable'
            );
        } else {
            $method = Closure::fromCallable($method);
            $this->methods[$methodName] = Closure::bind(
                $method,
                $this,
                get_class()
            );
        }

        return $this;
    }
    /**
     * Method match
     * 
     * @param string $subject - 
     * @param string $replace - 
     * @param string $pattern - 
     * 
     * @return string
     */
    public function match(
        string $subject,
        string $replace,
        string $pattern = '/input/i'
    ): string {
        $stringSubject = "";
        if (preg_match($pattern, $subject,  $matches, PREG_OFFSET_CAPTURE)) {
            $tabCount = count($matches);
            for ($i = 0; $i < $tabCount; $i++) {
                $matche = $matches[$i];
                if (is_array($matche) && 2 === count($matche)) {
                    $input = $matche[0];
                    $stringSubject = str_replace(
                        "{{" . $input . "}}",
                        $replace,
                        $subject
                    );
                }
            }
        } else {
            $stringSubject = $subject;
        }
        return $stringSubject;
    }

    /**
     * Method __call
     *
     * @param mixed $methodName - 
     * @param mixed $args       - 
     * 
     * @return mixed
     */
    public function __call($methodName, array $args = [])
    {
        if (isset($this->methods[$methodName])) {
            return call_user_func_array(
                $this->methods[$methodName],
                $args
            );
        }
        throw new  \RunTimeException(
            'There is no method with the given name to call'
        );
    }
    /**
     * From
     * 
     * @param string $name -
     * @param string $args -
     * 
     * @return mixed
     */
    public static function __callStatic($name, array $args = [])
    {
        if (!method_exists(new static, $name)) {
            return call_user_func_array(
                [new static, $name],
                $args
            );
        }
    }

    /**
     * Method isClosure
     *
     * @param mixed $t - 
     * 
     * @return void
     */
    protected function isClosure($t)
    {
        return $t instanceof \Closure;
    }

    /**
     * Method Validation
     * 
     * @param array  $filters - 
     * @param string $verbe   -
     * 
     * @return void
     */
    public function validation(array $filters = [], string $verbe = "post"): void
    {
        if (is_array($filters) && count($filters) > 0) {
            foreach ($filters as $key => $filter) {
                $this[$key] = $filter;
            }
            $this->filter($verbe);
        }
    }

    /**
     * Method method
     * 
     * @param string $verbe -
     * 
     * @return boolean
     */
    public function method(string $verbe = "post")
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($verbe);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->filters[] = $value;
            $this->keys[] = array_key_last($this->filters);
        } else {
            $this->filters[$offset] = $value;
            if (!in_array($offset, $this->keys)) {
                $this->keys[] = $offset;
            }
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->filters[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->filters[$offset]);
        unset($this->keys[array_search($offset, $this->keys)]);
        $this->keys = array_values($this->keys);
    }
    public function offsetGet($offset)
    {
        return isset($this->filters[$offset]) ? $this->filters[$offset] : null;
    }
    public function rewind()
    {
        $this->position = 0;
    }
    public function current()
    {
        return $this->filters[$this->keys[$this->position]];
    }
    public function key()
    {
        return $this->keys[$this->position];
    }
    public function next()
    {
        ++$this->position;
    }
    public function valid()
    {
        return isset($this->keys[$this->position]);
    }
    public function count(): int
    {
        return count($this->keys);
    }

    public function __destruct(){
    }
}
