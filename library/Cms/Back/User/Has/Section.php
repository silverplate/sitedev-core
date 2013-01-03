<?php

abstract class Core_Cms_Back_User_Has_Section extends App_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->addForeign(App_Cms_Back_User::createInstance())
             ->isPrimary(true);

        $this->addForeign(App_Cms_Back_Section::createInstance())
             ->isPrimary(true);
    }

    public static function getList($_where = array())
    {
        $where = $_where;

        if (isset($where['is_published'])) {
            $ids = App_Db::get()->getList(App_Db::get()->getSelect(
                self::getFirstForeignTbl(),
                self::getFirstForeignPri(),
                array('status_id' => $where['is_published'] ? 1 : 0)
            ));

            if (count($ids) == 0) {
                return array();
            } else {
                $where[self::getFirstForeignPri()] = $ids;
            }

            $ids = App_Db::get()->getList(App_Db::get()->getSelect(
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

        return parent::getList($where);
    }
}
