<?php

namespace Core\Cms\Ext;

abstract class Form extends \Ext\Form
{
    public function fillWithObject($_object = null)
    {
        if ($_object && $_object->id) {
            $this->fill($_object->toArray());

            $this->createButton('Сохранить', 'update');
            $this->createButton('Удалить', 'delete');

        } else {
            $this->createButton('Сохранить', 'insert');
        }
    }
}
