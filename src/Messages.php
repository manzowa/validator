<?php

namespace Manzowa\Validator;

/**
 * Trait Messages
 * 
 * PHP version 8.0.0
 * 
 * @category Manzowa\Validator
 * @package  Manzowa\Validator
 * @author   Christian Shungu <christianshungu@gmail.com>
 * @license  https://opensource.org/ BSD-3-Clause
 * @link     https://manzowa.com
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
        "size"    => "The size of the {{input}} field does not match the size required.",
        "confirm" => "The two {{input}} entered do not match.",
        "invalid" => "This {{input}} field is not validated.",
        "maxSizeFile" => "The file size for {{input}} exceeds the maximum allowed size of {{max}}.",
        "invalidFileType" => "The file type for {{input}} is invalid. Allowed types are: {{types}}.",
        "minLength" => "The {{input}} field must have at least {{min}} characters.",
        "maxLength" => "The {{input}} field cannot exceed {{max}} characters.",
    ];

    /**
     * Get the error message for a given key.
     *
     * @param string $key     The key for which to retrieve the message
     * @param array  $params Parameters to replace in the message (e.g. `{{input}}`, `{{max}}`)
     *
     * @return string
     */
    public function getMessage(string $key, array $params = []): string
    {
        // Vérifier si la clé du message existe
        if (isset($this->messages[$key])) {
            $message = $this->messages[$key];
            // Remplacer les placeholders dans le message par les paramètres fournis
            foreach ($params as $placeholder => $value) {
                $message = str_replace("{{" . $placeholder . "}}", $value, $message);
            }
            return $message;
        }
        // Si le message n'existe pas, retourner un message générique
        return "Invalid error message key: " . $key;
    }

    /**
     * Ajouter ou modifier un message personnalisé.
     *
     * @param string $key     La clé du message à ajouter ou modifier
     * @param string $message Le message personnalisé
     *
     * @return void
     */
    public function setMessage(string $key, string $message): void
    {
        $this->messages[$key] = $message;
    }
}