<?php

abstract class Core_Cms_Back_Page_404 extends App_Cms_Back_Page
{
    public function __construct()
    {
        parent::__construct(false);
        $this->setTemplate(TEMPLATES . 'back/404.xsl');
    }

    public function output()
    {
        header('HTTP/1.0 404 Not Found');
        parent::output();
    }
}
