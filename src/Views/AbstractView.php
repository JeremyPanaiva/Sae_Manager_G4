<?php
namespace Views;
abstract class AbstractView {
    function renderBody() {
        $template = file_get_contents($this->templatePath());

        foreach($this->templateKeys() as $key => $value){
            $template = str_replace("{{{$key}}}", $value, $template);
        }

        echo $template ;
    }
    abstract function templatePath() : string ;
    /** 
     * @return array<string, string>
     */
    abstract function templateKeys() : array ;
    function render(){
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
    } 

    function renderHeader(){
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0" >
           <link rel="stylesheet" href="/_assets/css/index.css">
            <title>Document</title>
        </head>
        <body>';
    }
    function renderFooter(){
        echo '</body>
        </html>';
    }

}