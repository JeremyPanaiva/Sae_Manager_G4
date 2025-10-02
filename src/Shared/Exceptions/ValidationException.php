<?php

namespace Shared\Exceptions;

class ValidationException extends \RuntimeException
{
    public function __construct(private string $fieldName, private string $fieldType, private string $error)
    {
        $message = sprintf("Mauvais typage %s il doit etre du type: %s , %s", $this->fieldName,$this->fieldType, $this->error);

        parent::__construct($message);
    }

}