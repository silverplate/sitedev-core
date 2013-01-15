<?php

abstract class Core_Cms_Mail_Recipient
{
    protected $_email;
    protected $_name;

    public function __construct($_email, $_name = null)
    {
        $this->_email = $_email;
        $this->_name = $_name;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($_name)
    {
        $this->_name = $_name;
    }

    public function isValid()
    {
        return Ext_String::isEmail($this->getEmail());
    }
}
