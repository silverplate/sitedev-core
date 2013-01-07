<?php

abstract class Core_Cms_Front_Template extends App_Model
{
    /**
     * @var App_File
     */
    protected $_file;

    protected $_originalFilePath;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addAttr('title', 'string');
        $this->addAttr('filename', 'string');
        $this->addAttr('is_document_main', 'boolean');
        $this->addAttr('is_multiple', 'boolean');
        $this->addAttr('is_published', 'boolean');
    }

    public function delete()
    {
//         App_Db::get()->execute(
//             'UPDATE ' . App_Cms_Front_Document::getTbl() .
//             ' SET ' . $this->getPrimaryKeyName() . ' = NULL WHERE ' . $this->getPrimaryKeyWhere()
//         );

        if ($this->getTemplateFile()) {
            $this->getTemplateFile()->delete();
        }

        return parent::delete();
    }

    public function update()
    {
        if ($this->getFilePath() != $this->_originalFilePath) {
            App_File::moveFile($this->_originalFilePath, $this->getFilePath());
        }

        return parent::update();
    }

    public function fillWithData(array $_data)
    {
        parent::fillWithData($_data);

        if (!isset($this->_originalFilePath)) {
            $this->_originalFilePath = $this->getFilePath();
        }
    }

    public static function getFolder()
    {
        return rtrim(TEMPLATES, '\\/');
    }

    public function getFilePath()
    {
        return self::getFolder() . '/' . $this->filename;
    }

    public function getTemplateFile()
    {
        if (!isset($this->_file)) {
            $this->_file = is_file($this->getFilePath())
                         ? new App_File($this->getFilePath())
                         : false;
        }

        return $this->_file;
    }

    public function getContent()
    {
        return $this->getTemplateFile()
             ? file_get_contents($this->getTemplateFile()->getPath())
             : false;
    }

    public function saveContent($_content)
    {
        $content = str_replace(array("\r\n", "\r"), "\n", $_content);
        $content = preg_replace('~[/n]{3,}~', "\n\n", $content);

        Ext_File::write($this->getFilePath(), $content);
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if ($this->isDocumentMain) {
            $attrs['prefix'] = 'о';
        }

        return parent::getBackOfficeXml($_xml, $attrs);
    }

    public function checkUnique()
    {
        return self::isUnique(
            'filename',
            $this->filename,
            $this->id ? $this->id : null
        );
    }
}
