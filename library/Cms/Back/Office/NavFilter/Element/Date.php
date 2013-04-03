<?php

abstract class Core_Cms_Back_Office_NavFilter_Element_Date
extends App_Cms_Back_Office_NavFilter_Element
{
    /**
     * @var string
     */
    protected $_dbAttrType;

    /**
     * @var integer
     */
    protected $_from;

    /**
     * @var integer
     */
    protected $_till;

    public function __construct($_dbAttr = null, $_dbAttrType = null, $_title = null)
    {
        parent::__construct($_dbAttr, $_title);
        $this->_dbAttrType = $_dbAttrType == 'sql' ? $_dbAttrType : 'integer';
    }

    public function run()
    {
        if (!empty($_POST['filter_from'])) {
            $this->_from = Ext_Date::getDate($_POST['filter_from']);

        } else if (!empty($_COOKIE['filter-from'])) {
            $this->_from = Ext_Date::getDate($_COOKIE['filter-from']);

        } else {
            $this->_from = false;
        }

        if ($this->_from) {
            $this->_from = mktime(
                0, 0, 0,
                date('n', $this->_from),
                date('j', $this->_from),
                date('Y', $this->_from)
            );
        }

        if (!empty($_POST['filter_till'])) {
            $this->_till = Ext_Date::getDate($_POST['filter_till']);

        } else if (!empty($_COOKIE['filter-till'])) {
            $this->_till = Ext_Date::getDate($_COOKIE['filter-till']);

        } else {
            $this->_till = false;
        }

        if ($this->_till) {
            $this->_till = mktime(
                23, 59, 59,
                date('n', $this->_till),
                date('j', $this->_till),
                date('Y', $this->_till)
            );
        }
    }

    public function getSqlWhere()
    {
        $where = array();

        if (!empty($this->_from)) {
            $value = $this->_dbAttrType == 'sql'
                   ? date('Y-m-d H:i:s', $this->_from)
                   : $this->_from;

            $where[] = $this->_dbAttr . ' >= ' . Ext_Db::escape($value);
        }

        if (!empty($this->_till)) {
            $value = $this->_dbAttrType == 'sql'
                   ? date('Y-m-d H:i:s', $this->_till)
                   : $this->_till;

            $where[] = $this->_dbAttr . ' <= ' . Ext_Db::escape($value);
        }

        return $where;
    }

    public function getXml()
    {
        $attrs = array(
            'type' => 'date',
            'today' => date('Y-m-d'),
            'week' => date('Y-m-d', time() - 60 * 60 * 24 * 7),
            'month' => date('Y-m-d', time() - 60 * 60 * 24 * 30)
        );

        if (!empty($this->_from)) {
            $attrs['from'] = date('Y-m-d', $this->_from);
        }

        if (!empty($this->_till)) {
            $attrs['till'] = date('Y-m-d', $this->_till);
        }

        return Ext_Xml::node(
            'filter-param',
            Ext_Xml::notEmptyCdata('title', $this->_title),
            $attrs
        );
    }
}
