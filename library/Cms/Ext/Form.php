<?php

class Core_Cms_Ext_Form extends Ext_Form
{
    public function fillWithObject($_object)
    {
        $this->fill($_object->toArray());

        if ($_object->id) {
            $this->createButton('Сохранить', 'update');
            $this->createButton('Удалить', 'delete');

        } else {
            $this->createButton('Сохранить', 'insert');
        }
    }
}
