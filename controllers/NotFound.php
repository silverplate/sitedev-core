<?php

abstract class Core_NotFoundController extends CommonController
{
    public function execute()
    {
        parent::execute();
        $this->setRootName('page-not-found');
    }

    public function output($_createCache = true)
    {
        header('HTTP/1.0 404 Not Found');
        return parent::output($_createCache);
    }
}
