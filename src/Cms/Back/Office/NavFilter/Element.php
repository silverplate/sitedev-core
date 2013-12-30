<?php

abstract class \Core\Cms\Back\Office_NavFilter_Element
{
    /**
     * @var string
     */
    protected $_dbAttr;

    /**
     * @var string
     */
    protected $_title;

    /**
     * @var string|false
     */
    protected $_value = false;

    public function __construct($_dbAttr = null, $_title = null)
    {
        if ($_dbAttr) {
            $this->_dbAttr = $_dbAttr;
        }

        if ($_title) {
            $this->_title = $_title;
        }
    }

    public function getName()
    {
        return $this->_dbAttr;
    }

    public function run()
    {
        $name = $this->getName();

        if (isset($_POST["filter_$name"]) && $_POST["filter_$name"] != "") {
            $this->_value = $_POST["filter_$name"];

        } else if (isset(
            $_COOKIE["filter-$name"]) &&
            $_COOKIE["filter-$name"] != ""
        ) {
            $this->_value = preg_replace(
                '/%u([0-9A-F]{4})/se',
                'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))',
                $_COOKIE["filter-$name"]
            );

        } else {
            $this->_value = false;
        }
    }

    public function getSqlWhere()
    {
        $where = array();

        if ($this->_value !== false) {
            $where[] = $this->_dbAttr . ' LIKE ' .
                       \Ext\Db::escape('%' . $this->_value . '%');
        }

        return $where;
    }

    public function getXml()
    {
        return \Ext\Xml::node(
            'filter-param',

            \Ext\Xml::notEmptyCdata('title', $this->_title) .
            \Ext\Xml::notEmptyCdata('value', $this->_value),

            array('name' => $this->_dbAttr, 'type' => 'string')
        );
    }
}
