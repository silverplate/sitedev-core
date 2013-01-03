<?php

abstract class Core_Cms_Front_Document_Controller_NotFound
extends App_Cms_Front_Document_Controller_Common
{
    public function execute()
    {
        parent::execute();
        $this->setRootName('page-not-found');
    }

    public function output()
    {
        header('HTTP/1.0 404 Not Found');
        return parent::output();
    }
}
