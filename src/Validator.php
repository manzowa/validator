<?php

namespace Manzowa\Validator;

use \Closure;
use \ArrayAccess;
use \Iterator;
use \Countable;


/**
 * Class Validator
 *
 * A flexible validation framework that allows dynamic field validation, error handling,
 * custom methods, and supports various HTTP request methods.
 *
 * PHP version 8.0.0
 *
 * @category Manzowa\Validator
 * @package  Manzowa\Validator
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://manzowa.com
 */
class Validator implements ArrayAccess, Iterator, Countable
{
    use Messages;
    use Filter;

    protected array $rules = []; 

    protected ?object $object;

    /** @var array $errors Holds error messages for each field */
    protected array $errors = [];

    /** @var array $methods Stores dynamically bound methods */
    protected array $methods = [];

    /** @var array $results Stores the validated field values */
    protected array $results = [];

    /** @var array $filters Stores the field validation rules */
    protected array $filters = [];

    /** @var array $keys Stores keys (field names) for filters */
    protected array $keys = [];

    /** @var array $inputs Stores input values for validation */
    protected array $inputs = [];

    /** @var string $field Stores the current field being processed */
    protected string $field = "";

    /** @var int $position Current position in the iterator */
    protected int $position = 0;

    /** @var string $requestMethod Current HTTP request method (e.g., POST, GET) */
    protected string $requestMethod = "post";


    /**
     * Constructor method to initialize the Validator.
     *
     * @param object|null $object Optional object to bind validation logic to
     * @param array $rules Optional validation rules per field
     */
    public function __construct(array $rules = [], ?object $object = null)
    {
        $this->rules  = $rules;
        $this->object = $object;
        $this->position = 0;
        $this->keys = array_keys($this->filters);
    }
    /**
     * Set validation rules for specific fields.
     *
     * @param string $field The field name
     * @param array  $rules The validation rules
     *
     * @return self
     */
    public function setRules(string $field, array $rules): self
    {
        $this->rules[$field] = $rules;
        return $this;
    }
    /**
     * Get validation rules for a specific field.
     *
     * @param string $field The field name
     *
     * @return array The validation rules for the field
     */
    public function getRules(string $field): array
    {
        return $this->rules[$field] ?? [];
    }

    /**
     * Set the HTTP method for filtering (POST, GET, etc.).
     *
     * @param string $method The HTTP method (default: "post")
     *
     * @return self
     */
    public function filter(string $method = "post"): self
    {
        $this->setRequestMethod($method);
        foreach ($this as $key => $callback) {
            $this->setField($key);
            if ($this->isClosure($callback)) {
                $methodName = "method" . ucfirst(trim($key));
                $this->bindMethod($methodName, $callback);
                $this->addInput($key);
                $this->{$methodName}();
            }
          
        }
        return $this;
    }

    /**
     * Set the current field being validated.
     *
     * @param string $field The field name
     *
     * @return self
     */
    public function setField(string $field): self
    {
        if ($field) {
            $this->field = $field;
        }
        return $this;
    }

    public function setResult(string $key, mixed $value): self 
    {
        $this->results[$key]= $value;
        return $this;
    }

    /**
     * Add input data to the filter.
     * This method pulls data for a specific field from the current request method.
     *
     * @param string $name The field name
     *
     * @return self
     */
    public function addInput(string $name): self
    {
        if ($name) {
            $inputValue = $this->inputed(
                $this->getRequestMethod(),
                $name,
                FILTER_SANITIZE_SPECIAL_CHARS
            );
            $this->inputs[$name] = $inputValue;
        }
        return $this;
    }
    public function setInput(string $name, mixed $value): self {
        $this->inputs[$name] = $value;
        return $this;
    }
    /**
     * setInputs data to the filter.
     *
     * @param array $inputs
     *
     * @return self
     */
    public function setInputs(array $inputs = []): self
    {
        $this->inputs = $inputs;
        return $this;
    }


    /**
     * Get the current field name being processed.
     *
     * @return string The field name
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the value for the current field.
     * Returns an empty string if the field is not set.
     *
     * @return mixed The value of the current field
     */
    public function getValue()
    {
        return $this->inputs[$this->getField()] ?? "";
    }

    /**
     * Get the value for a specific field (or "tampon").
     *
     * @param string|null $name The field name (optional)
     *
     * @return string The field value
     */
    public function getTampon(?string $name = ""): string
    {
        return $name ? ($this->inputs[$name] ?? "") : "";
    }

    /**
     * Get the error message associated with a given field.
     *
     * @param string|null $name The field name (optional)
     *
     * @return string The error message for the field
     */
    public function getMessage(?string $name = ""): string
    {
        return $this->messages[$name] ?? "";
    }

    /**
     * Add the field value to the results if no errors are found.
     *
     * @return self
     */
    public function get(): self
    {
        if (empty($this->errors)) {
            $this->results[$this->getField()] = $this->getValue();
        }
        return $this;
    }

    /**
     * Check if the validation failed (i.e., if any errors exist).
     *
     * @return bool True if validation failed, false otherwise
     */
    public function failed(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get the results of the validation, merging them with sanitized data.
     *
     * @return array The validation results (field names and their values)
     */
    public function results(): array
    {
        return $this->results;
    }

    /**
     * Get the validation errors as an associative array.
     *
     * @return array An array of errors where keys are field names and values are error messages
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Add an error message for a given field.
     *
     * @param string $name    The field name
     * @param string $message The error message
     *
     * @return self
     */
    public function addError(string $name, string $message): self
    {
        if ($name) {
            $this->errors[$name] = $this->match($message, $name);
        }
        return $this;
    }

    /**
     * Check if a specific field has an error.
     *
     * @param string $name The field name
     *
     * @return bool True if the field has an error, false otherwise
     */
    public function hasError(string $name): bool
    {
        return isset($this->errors[$name]);
    }

    /**
     * Get the current request method (POST, GET, etc.).
     *
     * @return string The HTTP request method
     */
    public function getRequestMethod(): string
    {
        return match ($this->requestMethod) {
            'post' => INPUT_POST,
            'get' => INPUT_GET,
            'cookie' => INPUT_COOKIE,
            'server' => INPUT_SERVER,
            'env' => INPUT_ENV,
            default => throw new \Exception('Unsupported request method'),
        };
    }

    /**
     * Set the request method for filtering.
     *
     * @param string $method The HTTP request method (e.g., POST, GET)
     *
     * @return self
     */
    public function setRequestMethod(string $method): self
    {
        $this->requestMethod = $method;
        return $this;
    }

    /**
     * Check if the validation error for the current field has already been set.
     *
     * @return bool True if no error exists for the field, false otherwise
     */
    public function isNotAlreadyError(): bool
    {
        return !$this->hasError($this->getField());
    }

    /**
     * Bind a closure method to the Validator class.
     *
     * @param string $methodName The name of the method
     * @param Closure $method The closure to bind
     *
     * @return self
     */
    public function bindMethod(string $methodName, Closure $method): self
    {
        if (!$this->isClosure($method)) {
            throw new \InvalidArgumentException('Second param must be callable');
        }

        $this->methods[$methodName] = Closure::bind($method, $this, get_class());
        return $this;
    }

    /**
     * Handle dynamic method calls for the Validator class.
     *
     * @param string $methodName The method name
     * @param array $args The arguments for the method
     *
     * @return mixed The result of the method call
     *
     * @throws \RuntimeException If the method does not exist
     */
    public function __call($methodName, array $args = [])
    {
        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
        throw new \RuntimeException('Method not found');
    }
    /**
     * Handle dynamic static method calls for the Validator class.
     *
     * @param string $name The static method name
     * @param array $args The arguments for the method
     *
     * @return mixed The result of the method call
     */
    public static function __callStatic($name, array $args = [])
    {
        if (method_exists(new static, $name) && is_array($args)) {
            return call_user_func_array([new static, $name], $args);
        }
    }
    /**
     * Check if the value is a closure.
     *
     * @param mixed $value The value to check
     *
     * @return bool True if the value is a closure, false otherwise
     */
    protected function isClosure($value): bool
    {
        return $value instanceof \Closure;
    }
    /**
     * Validate input fields based on the provided filters.
     *
     * @param array $filters The list of filters to apply
     * @param string $method The HTTP method (e.g., POST, GET) (default: POST)
     */
    public function validation(array $filters = [], string $method = "post"): void
    {
        foreach ($filters as $key => $filter) {
            $this[$key] = $filter;
        }
        $this->filter($method);
    }

    /**
     * Check if the current request method matches the expected method.
     *
     * @param string $method The expected HTTP method
     *
     * @return bool True if the methods match, false otherwise
     */
    public function method(string $method = "post"): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }
    public function offsetSet(mixed $offset, mixed $value): void
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
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->filters[$offset]);
    }
    public function offsetUnset(mixed $offset): void
    {
        unset($this->filters[$offset]);
        unset($this->keys[array_search($offset, $this->keys)]);
        $this->keys = array_values($this->keys);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->filters[$offset] ?? null;
    }
    // Iterator methods
    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): mixed
    {
        return $this->filters[$this->keys[$this->position]] ?? null;
    }

    public function key(): mixed
    {
        return $this->keys[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }
    // Countable method
    public function count(): int
    {
        return count($this->keys);
    }
    /**
     * Validate the input fields based on the defined rules.
     * 
     * @param array $filters The list of filters to apply
     * @param string $method The HTTP method (e.g., POST, GET) (default: POST)
     *
     * @return self
     */
    public function validate(array $filters = [], string $method = "post"): self
    {
        if (count($filters)> 0) {
            foreach ($filters as $key => $filter) {
                $this[$key] = $filter;
            }
            $this->filter($method);

        } else {
            foreach ($this->rules as $field => $rules) 
            {
                $this->setField($field);
                foreach ($rules as $rule => $param) {
                    $this->applyRule($field, $rule, $param);
                }
            }
        }
        return $this;
    }
     /**
     * Apply a specific rule to the field.
     *
     * @param string $field The field name
     * @param string $rule The rule to apply (e.g., "required", "minLength")
     * @param mixed $param The parameter for the rule (e.g., minimum length)
     */
    public function applyRule(string $field, string $rule, $param): void
    {
        switch ($rule) {
            case 'required':
                if (empty($this->getValue())) {
                    $this->addError($field, $this->getMessage('empty'));
                }
                break;

            case 'minLength':
                if (strlen($this->getValue()) < $param) {
                    $this->addError($field, $this->getMessage('minLength', ['min' => $param]));
                }
                break;

            case 'maxLength':
                if (strlen($this->getValue()) > $param) {
                    $this->addError($field, $this->getMessage('maxLength', ['max' => $param]));
                }
                break;

            case 'numeric':
                if (!is_numeric($this->getValue())) {
                    $this->addError($field, $this->getMessage('number'));
                }
                break;

            case 'email':
                if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, $this->getMessage('invalid'));
                }
                break;

            case 'regex':
                if (!preg_match($param, $this->getValue())) {
                    $this->addError($field, $this->getMessage('invalid'));
                }
                break;

            default:
                // Handle custom validation logic here
                break;
        }
    }
    // Destructor
    public function __destruct()
    {
    }

    public function inputed($inputType, $key, $filter = FILTER_DEFAULT) 
    {
        $raw_array = filter_input($inputType, $key, $filter, FILTER_REQUIRE_ARRAY);
        if ($raw_array !== null && $raw_array !== false) {
            return $raw_array;
        }
        $raw = filter_input($inputType, $key, $filter);
        if ($raw !== null && $raw !== false) {
            return $raw;
        }
        return null;
    }

    public function compareData(array $form, array $origin): array
    {
        $normalize = function(array $data): array {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = implode(',', $value);
                }
            }
            return $data;
        };

        $normalizedForm   = $normalize($form);
        $normalizedOrigin = $normalize($origin);

        return array_diff_assoc($normalizedForm, $normalizedOrigin);
    }
}

?>
