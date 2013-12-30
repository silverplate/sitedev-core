<?php

namespace Core\Cms\Front;

abstract class Controller extends \App\ActiveRecord
{
    protected $_originalFilePath;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addAttr('type_id', 'integer');
        $this->addAttr('title', 'string');
        $this->addAttr('filename', 'string');
        $this->addAttr('is_document_main', 'boolean');
        $this->addAttr('is_multiple', 'boolean');
        $this->addAttr('is_published', 'boolean');
    }

    public function getClassName()
    {
        $class = \Ext\File::computeName($this->filename);

        if ($this->typeId == 1)      return 'Controller\\' . $class;
        else if ($this->typeId == 2) return 'Helper\\' . $class;
        else                         throw new \Exception('Unkown controller type');
    }

    public static function getPathByType($_id)
    {
        switch ($_id) {
            case 1: return CONTROLLERS;
            case 2: return HELPERS;
        }

        return false;
    }

    public function getFolder()
    {
        return self::getPathByType($this->type_id);
    }

    public function getFilename()
    {
        return $this->getFolder() && $this->filename
             ? $this->getFolder() . $this->filename
             : false;
    }

    public function getContent()
    {
        return $this->getFilename() && is_file($this->getFilename())
             ? file_get_contents($this->getFilename())
             : false;
    }

    public function saveContent($_content)
    {
        $content = str_replace(array("\r\n", "\r"), "\n", $_content);
        $content = preg_replace('~[\n]{3,}~', "\n\n", $content);

        \Ext\File::write($this->getFilename(), $content);
    }

    public function checkUnique()
    {
        $where = array(
            'type_id' => $this->typeId,
            'filename' => $this->filename
        );

        if ($this->id) {
            $where[] = $this->getPrimaryKeyWhereNot();
        }

        return 0 == count(self::getList($where, array('limit' => 1)));
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if ($this->typeId == 1)      $attrs['prefix'] = 'с';
        else if ($this->typeId == 2) $attrs['prefix'] = 'б';

        return parent::getBackOfficeXml($_xml, $attrs);
    }

    public function delete()
    {
//         \Ext\Db::get()->execute(
//             'UPDATE ' . \App\Cms\Front\Document::getTbl() .
//             ' SET ' . $this->getPrimaryKeyName() . ' = NULL WHERE ' . $this->getPrimaryKeyWhere()
//         );

//         \Ext\Db::get()->execute(
//             'UPDATE ' . \App\Cms\Front\Data::getTbl() .
//             ' SET ' . $this->getPrimaryKeyName() . ' = NULL WHERE ' . $this->getPrimaryKeyWhere()
//         );

        \Ext\File::deleteFile($this->getFilename());

        return parent::delete();
    }

    public function fillWithData(array $_data)
    {
        parent::fillWithData($_data);

        if (!isset($this->_originalFilePath)) {
            $this->_originalFilePath = $this->getFilename();
        }
    }

    public function update()
    {
        if ($this->getFilename() != $this->_originalFilePath) {
            \Ext\File::moveFile(
                $this->_originalFilePath,
                $this->getFilename()
            );
        }

        return parent::update();
    }

    public static function getList($_where = array(), $_params = array())
    {
        $params = $_params;
        if (!isset($params['order'])) {
            $params['order'] = 'type_id, title';
        }

        return parent::getList($_where, $params);
    }
}
