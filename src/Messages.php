<?php

namespace Wenzawa\Validator;

/**
 * Class trait Messages
 * 
 * PHP version 8.0.0
 * 
 * @category Wenzawa\Validator;
 * @package  Wenzawa\Validator;
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://cshungu.fr
 */
trait Messages
{
    /**
     * Variables messages
     *
     * @var array $messages
     */
    protected $messages = [
        "empty"   => "This {{input}} field is empty.",
        "number"  => "This {{input}} field does not match a number.",
        "size"    => "The size of the {{input}} field does not match the size required. ",
        "confirm" => "The two {{input}} entered do not match.",
        "invalid" => "This {{input}} field is not validated.",
    ];
}
