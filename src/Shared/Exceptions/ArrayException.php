<?php

namespace Shared\Exceptions;

class ArrayException extends \RuntimeException
{
    /**
     * @param ValidationException[] $validationException
     */
    public function __construct(private array $validationException)
    {
        parent::__construct("probleme de validation");


    }

    /**
     * @return ValidationException[]
     */
    public function getExceptions(): array
    {
        return $this->validationException;
    }
}