<?php

namespace Views\Base;

use Models\User\User;
use Views\AbstractView;

class HeaderView extends AbstractView
{

    public function __construct(?User $user = null)
    {
    }

    function templatePath(): string
    {
        return __DIR__ . '/header.html';
    }

    function templateKeys(): array
    {
        return [];
    }
}