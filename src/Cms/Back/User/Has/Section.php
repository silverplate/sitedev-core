<?php

namespace Core\Cms\Back\User\Has;

abstract class Section extends \App\ActiveRecord
{
    public function __construct()
    {
        parent::__construct();

        $this->addForeign(\App\Cms\Back\User::createInstance())
             ->isPrimary(true);

        $this->addForeign(\App\Cms\Back\Section::createInstance())
             ->isPrimary(true);
    }

    public static function getList($_where = array(), $_params = array())
    {
        $where = $_where;

        if (isset($where['is_published'])) {
            $ids = \Ext\Db::get()->getList(\Ext\Db::get()->getSelect(
                self::getFirstForeignTbl(),
                self::getFirstForeignPri(),
                array('status_id' => $where['is_published'] ? 1 : 0)
            ));

            if (count($ids) == 0) {
                return array();
            } else {
                $where[self::getFirstForeignPri()] = $ids;
            }

            $ids = \Ext\Db::get()->getList(\Ext\Db::get()->getSelect(
                self::getSecondForeignTbl(),
                self::getSecondForeignPri(),
                array('is_published' => $where['is_published'] ? 1 : 0)
            ));

            if (count($ids) == 0) {
                return array();
            } else {
                $where[self::getSecondForeignPri()] = $ids;
            }

            unset($where['is_published']);
        }

        return parent::getList($where, $_params);
    }
}
