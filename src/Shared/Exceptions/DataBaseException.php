<?php

namespace Shared\Exceptions;

use RuntimeException;

class DataBaseException extends RuntimeException
{
    public function __construct(string $message = "Erreur de connexion à la base de données.")
    {
        parent::__construct($message);
    }
}
