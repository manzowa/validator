### monzowa\manzowa-validator (Version: 0.0.1)
--------------------------------------------------------------------
manzowa\manzowa-validator is a very small validation library, with the easiest and most usable API we could possibly create.
[![Source Code](https://manzowa.com/images/source.png)](https://github.com/manzowa/manzowa-validator)
[![Latest Version](https://manzowa.com/images/source.packagist.png)](https://packagist.org/packages/manzowa/manzowa-validator)
[![Software License](https://manzowa.com/images/license.png)](https://github.com/manzowa/manzowa-validator)

#### Functions
-----------------------

| Function                                                | Description                                                    |
| :------------------------------------------------------ | :------------------------------------------------------------- |
| errors()                                                | Get errors.                                                    |
| failed()                                                | boolean.                                                       |
| isEmail()                                               | It allows you to check if the e-mail address entered is valid  |
| isEmpty()                                               | It allows you to check if the field is empty                   |
| isNumber()                                              | It is used to check if the type of the value entered is number |
| method()                                                | boolean.                                                       |
| resuts()                                             | Get results.                                                 |
| same(string $name = "")                                 | It allows you to compare the value of two fields.              |
| size(int $digit = 8)                                    | It allows you to check the size of the entered value.          |
| validate(array $filters = [], string $verbe = "post") | ?.                                                             |

```
<?php 
    $validator = new Monzowa\Validator\Validator;

    if ($validator->method()) {
        $validator->validate(
            [
                "numero" => function () {
                    Validator::isEmpty()->isNumber()->get();
                },
                "code" => function () {
                    Validator::isEmpty()->isNumber()->size(4)->get();
                },
                "email" => function () {
                    Validator::isEmpty()->get();
                },
                "confirm_email" =>  function () {
                    Validator::isEmpty()->same("email")->get();
                },
                "password" => function () {
                    Validator::isEmpty()->get();
                },
                "confirm_password" => function () {
                    Validator::isEmpty()->get();
                }
            ]
        );
        if ($validator->failed()) {
            echo "<pre>";
            print_r($validator->errors());
            echo "</pre>";
        } else {
            echo "<pre>";
            print_r($validator->results());
            echo "</pre>";
        }
    }
?>
```
The simplest validation engine:
    - https://www.manzowa.com/validation
