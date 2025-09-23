<?php
namespace Views;
abstract class AbstractView implements View
{
    function renderBody(): void
    {
        $template = file_get_contents($this->templatePath());

        foreach($this->templateKeys() as $key => $value){
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        echo $template ;
    }
}