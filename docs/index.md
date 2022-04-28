# Wenzawa\Validator

## Example simple
----------------------------------------------------------------------------------------------

$resultats = [];
$validator = new Validator;

if ($validator->method()) {
    $validator->validation(
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
        print_r($validator->resultats());
        echo "</pre>";
    }
}