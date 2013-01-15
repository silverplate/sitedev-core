<?php

abstract class Core_ActiveRecord_Attribute
{
    protected $_name;
    protected $_type;
    protected $_value;
    protected $_isPrimary;
    protected $_length;

    public function __construct($_name, $_type)
    {
        $this->_name = $_name;
        $this->setType($_type);
    }

    public function setType($_type)
    {
        $this->_type = $_type == 'char' || $_type == 'varchar'
                     ? 'string'
                     : $_type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setValue($_value)
    {
        switch ($this->_type) {
            case 'integer':
                $this->_value = (string) $_value == '' ? $_value : (integer) $_value;
                break;

            case 'float':
                $this->_value = Ext_Number::number($_value);
                break;

            case 'boolean':
                $this->_value = $_value ? 1 : 0;
                break;

            default:
                $this->_value = $_value;
                break;
        }
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getSqlValue()
    {
        return (string) $this->_value == '' ? 'NULL' : Ext_Db::escape($this->_value);
    }

    public function isValue()
    {
        return (string) $this->_value != '';
    }

    public function getName()
    {
        return $this->_name;
    }

    public function isPrimary($_isPrimary = null)
    {
        if (is_null($_isPrimary)) {
            return $this->_isPrimary;

        } else {
            $this->_isPrimary = (boolean) $_isPrimary;
        }
    }

    public function setLength($_length)
    {
        $this->_length = $_length;
    }

    public function getLength()
    {
        return $this->_length;
    }
}
