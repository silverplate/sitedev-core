<?php

abstract class Core_Cms_Back_Office_NavFilter_Element_Multiple
extends App_Cms_Back_Office_NavFilter_Element
{
    /**
     * @var array|false
     */
    protected $_value = false;

    /**
     * @var array[array]
     */
    protected $_options = array();

    public function run()
    {
        $name = $this->getName();

        if (!empty($_POST["is_filter_$name"])) {
            $this->_value = empty($_POST["filter_$name"])
                          ? array()
                          : $_POST["filter_$name"];

        } else if (!empty($_COOKIE["is-filter-$name"])) {
            if (empty($_COOKIE["filter-$name"])) {
                $this->_value = array();

            } else {
                $this->_value = preg_replace(
                    '/%u([0-9A-F]{4})/se',
                    'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))',
                    $_COOKIE["filter-$name"]
                );

                if (strpos($this->_value, '|') !== false) {
                    $this->_value = explode('|', $this->_value);
                }
            }

        } else {
            $this->_value = false;
        }

        if ($this->_value !== false && !is_array($this->_value)) {
            $this->_value = array($this->_value);
        }
    }

    public function getSqlWhere()
    {
        $where = array();

        if ($this->_value !== false) {
            $where[] = $this->_value
                     ? $this->_dbAttr . ' IN (' . \Ext\Db::escape($this->_value) . ')'
                     : 'ISNULL(' . $this->_dbAttr . ')';
        }

        return $where;
    }

    public function getXml()
    {
        $attrs = array('name' => $this->_dbAttr, 'type' => 'multiple');

        if ($this->_value !== false) {
            $attrs['is-selected'] = true;
        }

        $xml = \Ext\Xml::cdata('title', $this->_title);

        foreach ($this->_options as $id => $title) {
            \Ext\Xml::append($xml, \Ext\Xml::cdata(
                'item',
                $title,
                array(
                    'value' => $id,
                    'is-selected' => $this->_value !== false &&
                                     in_array($id, $this->_value)
                )
            ));
        }

        return \Ext\Xml::node('filter-param', $xml, $attrs);
    }

    public function addOption($_id, $_title)
    {
        $this->_options[$_id] = $_title;
    }
}
