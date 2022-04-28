# Wenzawa\Validator (Version: 0.0.1)
--------------------------------------------------------------------
Wenzawa\Validator is a very small validation library, with the easiest and most usable API we could possibly create.

# Install
--------------------------------------------------------------------
To easily include Wenzawa\Validator into your project, install it via composer using the command line:
`composer require wenzawa\Validator`


## Functions
-----------------------

| Function                                                | Description                                                    |
| :------------------------------------------------------ | :------------------------------------------------------------- |
| errors()                                                | Get errors.                                                    |
| failed()                                                | boolean.                                                       |
| isEmail()                                               | It allows you to check if the e-mail address entered is valid  |
| isEmpty()                                               | It allows you to check if the field is empty                   |
| isNumber()                                              | It is used to check if the type of the value entered is number |
| method()                                                | boolean.                                                       |
| resultats()                                             | Get resultats.                                                 |
| same(string $name = "")                                 | It allows you to compare the value of two fields.              |
| size(int $digit = 8)                                    | It allows you to check the size of the entered value.          |
| validation(array $filters = [], string $verbe = "post") | ?.                                                             |

The simplest validation engine:
    - https://www.cshungu.fr
