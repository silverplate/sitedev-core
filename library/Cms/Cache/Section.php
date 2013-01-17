<?php

abstract class Core_Cms_Cache_Section
{
    protected $_uri;
    protected $_time;
    protected $_isWhole;
    protected $_isQueryImportant;

    public function __construct($_uri, $_time, $_isWhole = false, $_isQuery = false)
    {
        $this->_uri = $_uri;
        $this->_time = (integer) $_time;
        $this->_isWhole = (boolean) $_isWhole;
        $this->_isQueryImportant = (boolean) $_isQuery;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function getTime()
    {
        return (int) $this->_time;
    }

    public function isWhole()
    {
        return $this->_isWhole;
    }

    public function isQueryImportant()
    {
        return $this->_isQueryImportant;
    }
}
