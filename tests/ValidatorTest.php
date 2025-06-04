<?php

namespace Manzowa\Validator;

use \PHPUnit\Framework\TestCase;
use Manzowa\Validator\Validator;

class ValidatorTest extends TestCase
{
    protected Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
        if ($this->validator->method()) {
            $this->validator->validation(
                [
                    "numero" => function () {
                        Validator::isEmpty()->get();
                    },
                ]
            );
        }
        
    }
    public function testFailed()
    {
        $this->assertEquals(false, $this->validator->failed());
    }
}
