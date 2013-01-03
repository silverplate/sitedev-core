<?php

abstract class Core_ActiveRecord_Attribute
{
    private $_name;
    private $_type;
    private $_value;
    private $_isPrimary;

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
                $this->_value = (integer) $_value;
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
        return (string) $this->_value == '' ? 'NULL' : App_Db::escape($this->_value);
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
}
