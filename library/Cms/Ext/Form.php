<?php

abstract class Core_Cms_Ext_Form extends Ext_Form
{
    public function fillWithObject($_object)
    {
        if ($_object->id) {
            $this->fill($_object->toArray());

            $this->createButton('Сохранить', 'update');
            $this->createButton('Удалить', 'delete');

        } else {
            $this->createButton('Сохранить', 'insert');
        }
    }
}
