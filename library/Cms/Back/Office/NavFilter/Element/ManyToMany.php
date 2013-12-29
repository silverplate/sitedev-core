<?php

class Core_Cms_Back_Office_NavFilter_Element_ManyToMany
extends Core_Cms_Back_Office_NavFilter_Element_Multiple
{
    protected $_key;
    protected $_table;
    protected $_valueKey;
    protected $_linkTable;

    public function __construct($_title, $_key, $_table, $_valueKey, $_linkTable)
    {
        parent::__construct(null, $_title);

        $this->_key = $_key;
        $this->_table = $_table;
        $this->_valueKey = $_valueKey;
        $this->_linkTable = $_linkTable;
    }

    public function getSqlWhere()
    {
        $where = array();

        if ($this->_value !== false) {
            $p = \Ext\Db::get()->getPrefix();

            if ($this->_value) {
                $value = \Ext\Db::escape($this->_value);

                $ids = \Ext\Db::get()->getList("
                    SELECT   $this->_key
                    FROM     $this->_linkTable
                    WHERE    $this->_valueKey IN ($value)
                    GROUP BY $this->_key
                ");

            } else {
                $ids = \Ext\Db::get()->getList("
                    SELECT    $this->_key
                    FROM      $this->_table
                    LEFT JOIN $this->_linkTable
                    USING     ($this->_key)
                    WHERE     ISNULL($this->_valueKey)
                ");
            }

            if (count($ids) == 0) {
                return false;
            }

            $value = \Ext\Db::escape($ids);
            $where[] = "$this->_key IN ($value)";
        }

        return $where;
    }
}
