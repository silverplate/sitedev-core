<?php

abstract class Core_Model_Tree extends App_Model
{
    protected $_isChildren;

    public function isChildren($_exceptId = null)
    {
        if (is_null($this->_isChildren)) {
            $list = static::getList(array('parent_id' => $this->getId()));

            if (is_null($_exceptId)) {
                $this->_isChildren = ($list);

            } else if ($list) {
                $this->_isChildren = false;

                foreach ($list as $item) {
                    if ($item->getId() != $_exceptId) {
                        $this->_isChildren = true;
                        break;
                    }
                }

            } else {
                $this->_isChildren = false;
            }
        }

        return $this->_isChildren;
    }

    public static function getMultiAncestors($_ids)
    {
        $result = array();

        foreach ($_ids as $id) {
            if (!in_array($id, $result)) {
                $result = array_merge($result, static::getAncestors($id));
            }
        }

        return $result;
    }

    public static function getAncestors($_id)
    {
        $result = array();
        $key = static::getPri();

        $entry = \Ext\Db::get()->getEntry(\Ext\Db::get()->getSelect(
            static::getTbl(),
            array($key, 'parent_id'),
            array($key => $_id)
        ));

        if ($entry) {
            $result[] = $entry[$key];

            if ($entry['parent_id']) {
                $result = array_merge(
                    $result,
                    static::getAncestors($entry['parent_id'])
                );
            }
        }

        return $result;
    }
}
