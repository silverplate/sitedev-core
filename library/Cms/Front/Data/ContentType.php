<?php

abstract class Core_Cms_Front_Data_ContentType extends App_Model
{
    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addAttr('title', 'string');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    public static function import()
    {
        $result = array();
        $list = array(
            array('string', 'Строка', 1),
            array('text', 'Текст', 1),
            array('html', 'Визуальный редактор', 0),
            array('data', 'Дата', 0),
            array('integer', 'Целое число', 1),
            array('float', 'Дробное число', 0),
            array('xml', 'XML', 1)
        );

        foreach ($list as $item) {
            $type = self::createInstance();

            $type->id = $item[0];
            $type->title = $item[1];
            $type->isPublished = $item[2];

            if ($type->create()) {
                $result[] = $type->id;
            }
        }

        return $result;
    }

//     public function delete()
//     {
//         App_Db::get()->execute(
//             'UPDATE ' . App_Cms_Front_Data::getTbl() .
//             ' SET ' . $this->getPrimaryKeyName() . ' = NULL WHERE ' . $this->getPrimaryKeyWhere()
//         );

//         return parent::delete();
//     }
}
