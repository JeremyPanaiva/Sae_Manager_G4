<?php

namespace Views\Base;

use Couchbase\View;
use Views\AbstractView;

class FooterView extends AbstractView
{

    function templatePath(): string
    {
        return __DIR__ . '/footer.html';
    }

    function templateKeys(): array
    {
       return [];
    }
}