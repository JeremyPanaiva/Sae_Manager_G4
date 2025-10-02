<?php

namespace Views\Base;

use Models\User\User;
use Views\AbstractView;

abstract class BaseView extends AbstractView
{

    private HeaderView $header;
    private FooterView $footer;

    protected ?User $user;
    public function __construct()
    {
       $this->user = null ;

    }

    function render(): string{
        $this->header = new HeaderView();
        $this->footer = new FooterView();
        return $this->header->renderBody().
        $this->renderBody().
        $this->footer->renderBody();
    }

    function setUser(?User $user){
        $this->user = $user;
    }
}