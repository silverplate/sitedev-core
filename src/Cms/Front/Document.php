<?php

namespace Core\Cms\Front;

abstract class Document extends \App\ActiveRecord\Tree
{
    const FOLDER = 'f';

    protected $_language;

    /**
     * @var \App\Cms\Front\Controller
     */
    protected $_controller;

    /**
     * @var \App\Cms\Front\Template
     */
    protected $_template;

    protected $_linkParams = array(
        'navigations' => '\App\Cms\Front\Document\Has\Navigation'
    );

    /**
     * @var \App\Cms\Front\Data[]
     */
    protected $_data;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addForeign(\App\Cms\Front\Controller::createInstance());
        $this->addForeign(\App\Cms\Front\Template::createInstance());
        $this->addAttr('parent_id', 'string');
        $this->addAttr('auth_status_id', 'integer');
        $this->addAttr('title', 'string');
        $this->addAttr('title_compact', 'string');
        $this->addAttr('folder', 'string');
        $this->addAttr('link', 'string');
        $this->addAttr('uri', 'string');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    /**
     * @return \App\Cms\Front\Data[]
     */
    public function getData()
    {
        if (!isset($this->_data)) {
            $this->_data = \App\Cms\Front\Data::getList(array(
                $this->getPrimaryKeyName() => $this->id
            ));
        }

        return $this->_data;
    }

    public function getFilePath()
    {
        return DOCUMENT_ROOT . self::FOLDER . rtrim($this->uri, '/') . '/';
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        global $gIsUsers;

        $attrs = $_attrs;

        if (
            !empty($gIsUsers) &&
            $this->authStatusId != \App\Cms\User::AUTH_GROUP_ALL &&
            \App\Cms\User::getAuthGroupTitle($this->authStatusId)
        ) {
            $attrs['prefix'] = \Ext\String::toLower(\Ext\String::getPart(
                \App\Cms\User::getAuthGroupTitle($this->authStatusId), 0, 1
            ));
        }

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        \Ext\Xml::append(
            $xml,
            \Ext\Xml::notEmptyCdata('title-compact', $this->titleCompact)
        );

        return parent::getBackOfficeXml($xml, $attrs);
    }

    public function getLang()
    {
        if (is_null($this->_language)) {
            $this->_language = '';

            if (\App\Cms\Front\Office::getLanguages()) {
                foreach (array_keys(\App\Cms\Front\Office::getLanguages()) as $i) {
                    $pos = strpos($this->uri, "/$i/");

                    if ($pos !== false && $pos == 0) {
                        $this->_language = $i;
                        break;
                    }
                }
            }
        }

        return $this->_language;
    }

    public function getUri()
    {
        return $this->getLang()
             ? substr($this->uri, strlen($this->getLang()) + 1)
             : $this->uri;
    }

    public function getUrl()
    {
        $langs = \App\Cms\Front\Office::getLanguages();

        return $this->getLang()
             ? 'http://' . $langs[$this->getLang()][0] . $this->getUri()
             : $this->getUri();
    }

    private function _computeUri($_parentUri = null)
    {
        if (!is_null($_parentUri)) {
            $uri = $_parentUri;

        } else if ($this->parentId) {
            $parent = self::getById($this->parentId);
            if ($parent) $uri = $parent->uri;
        }

        if (empty($uri)) $uri = '/';
        $folder = $this->folder;

        if ($folder != '/') {
            $uri .= $folder;

            if (strpos($folder, '.') === false) {
                $uri .= '/';
            }
        }

        $this->uri = $uri;
    }

    public static function updateChildrenUri($_id = null)
    {
        $id = '';
        $uri = '';

        if (!is_null($_id)) {
            $obj = self::getById($_id);

            if ($obj) {
                $id = $_id;
                $uri = $obj->uri;

            } else {
                return false;
            }
        }

        $list = self::getList(array('parent_id' => $id));

        foreach ($list as $item) {
            $folder = $item->folder;

            if ($folder != '/' && strpos($folder, '.') === false) {
                $folder .= '/';
            }

            $item->updateAttr('uri', $uri . $folder);
            self::updateChildrenUri($item->getId());
        }
    }

    public function create()
    {
        $this->_computeUri();
        return parent::create();
    }

    public function update()
    {
        $path = $this->getFilePath();
        $this->_computeUri();
        $root = DOCUMENT_ROOT . self::FOLDER . '/';

        if (
            $path != $this->getFilePath() &&
            is_dir($path) &&
            $this->getFilePath() != $root &&
            $path != $root
        ) {
            \Ext\File::moveDir($path, $this->getFilePath());
        }

        parent::update();
        self::updateChildrenUri($this->getId());
    }

    public function delete()
    {
        foreach (self::getList(array('parent_id' => $this->getId())) as $item) {
            $item->delete();
        }

        foreach (\App\Cms\Front\Data::getList($this->getPrimaryKeyWhere()) as $item) {
            $item->delete();
        }

        \Ext\File::deleteDir($this->getFilePath());

        return parent::delete();
    }

    /**
     * @return \App\Cms\Front\Controller
     */
    public function getController()
    {
        if (is_null($this->_controller)) {
            $this->_controller = $this->frontControllerId
                               ? \App\Cms\Front\Controller::getById($this->frontControllerId)
                               : false;
        }

        return $this->_controller;
    }

    public function getControllerFile()
    {
        return $this->getController()
             ? $this->getController()->getFilename()
             : false;
    }

    /**
     * @param \App\Cms\Front\Controller $_controller
     * @param \App\Cms\Front\Document $_document
     * @return \App\Cms\Front\Document\Controller|false
     */
    public static function initController(\App\Cms\Front\Controller $_controller, &$_document)
    {
        require_once $_controller->getFilename();

        $class = $_controller->getClassName();

        if (class_exists($class)) {
            return new $class($_document);
        }

        return false;
    }

    /**
     * @return \App\Cms\Front\Template
     */
    public function getTemplate()
    {
        if (is_null($this->_template)) {
            $this->_template = $this->frontTemplateId
                             ? \App\Cms\Front\Template::getById($this->frontTemplateId)
                             : false;
        }

        return $this->_template;
    }

    public function checkUnique()
    {
        $where = array(
            'parent_id' => $this->parentId,
            'folder' => $this->folder
        );

        if ($this->id) {
            $where[] = $this->getPrimaryKeyWhereNot();
        }

        return 0 == count(self::getList($where, array('limit' => 1)));
    }

    public function checkFolder()
    {
        return $this->folder == '/' || \Ext\File::checkName($this->folder);
    }

    public function checkRoot()
    {
        return $this->folder && ($this->folder != '/' || !$this->parentId);
    }
}
