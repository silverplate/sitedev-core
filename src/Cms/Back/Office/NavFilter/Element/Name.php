<?php

abstract class \Core\Cms\Back\Office_NavFilter_Element_Name
extends \App\Cms\Back\Office_NavFilter_Element
{
    public function __construct($_title = null)
    {
        parent::__construct(null, $_title);
    }

    public function getSqlWhere()
    {
        $where = array();

        if ($this->_value !== false) {
            $where[] = 'CONCAT_WS(" ", last_name, first_name, middle_name) LIKE ' .
                       \Ext\Db::escape('%' . $this->_value . '%');
        }

        return $where;
    }
}
