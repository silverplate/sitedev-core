<?php

abstract class Core_Cms_Front_Template extends App_Model
{
    /**
     * @var App_File
     */
    protected $_file;

    protected $_originalFilename;

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

        if ($this->getFile()) {
            $this->getFile()->delete();
        }

        return parent::delete();
    }

    public function update()
    {
        if ($this->filename != $this->_originalFilename) {
            App_File::moveFile(
                $this->getFolder() . '/' . $this->_originalFilename,
                $this->getFilePath()
            );
        }

        return parent::update();
    }

    public function fillWithData(array $_data)
    {
        parent::fillWithData($_data);

        if (!isset($this->_originalFilename)) {
            $this->_originalFilename = $this->filename;
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

    public function getFile()
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
        return $this->getFile()
             ? file_get_contents($this->getFile()->getPath())
             : false;
    }

    public function setContent($_content)
    {
        App_File::write($this->getFilePath(), $_content);
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if ($this->isDocumentMain) {
            $attrs['prefix'] = 'о';
        }

        return parent::getBackOfficeXml($_xml, $attrs);
    }
}
