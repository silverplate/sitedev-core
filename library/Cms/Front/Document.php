<?php

abstract class Core_Cms_Front_Document extends App_Model
{
    protected $_isChildren;
    protected $_language;

    /**
     * @var App_Cms_Front_Controller
     */
    protected $_controller;

    /**
     * @var App_Cms_Front_Template
     */
    protected $_template;

    protected $_linkParams = array(
        'navigations' => 'App_Cms_Front_Document_Has_Navigation'
    );

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addForeign(App_Cms_Front_Controller::createInstance());
        $this->addForeign(App_Cms_Front_Template::createInstance());
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

    public function getFilePath()
    {
        return rtrim(DOCUMENT_ROOT . 'f/' . ltrim($this->uri, '/'), '/') . '/';
    }

    public function getBackOfficeXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;

        if (
            defined('IS_USERS') &&
            IS_USERS &&
            $this->authStatusId != App_Cms_User::AUTH_GROUP_ALL &&
            App_Cms_User::getAuthGroupTitle($this->authStatusId)
        ) {
            $attrs['prefix'] = Ext_String::toLower(
                substr(App_Cms_User::getAuthGroupTitle($this->authStatusId), 0, 1)
            );
        }

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        Ext_Xml::append(
            $xml,
            Ext_Xml::notEmptyCdata('title-compact', $this->titleCompact)
        );

        return parent::getBackOfficeXml($xml, $attrs);
    }

    public function getLang()
    {
        global $g_langs;

        if (is_null($this->_language)) {
            $this->_language = '';

            if (isset($g_langs) && $g_langs) {
                foreach (array_keys($g_langs) as $i) {
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
        global $g_langs;

        return $this->getLang()
             ? 'http://' . $g_langs[$this->getLang()][0] . $this->getUri()
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

        if ($path != $this->getFilePath() && is_dir($path)) {
            Ext_File::moveDir($path, $this->getFilePath());
        }

        parent::update();
        self::updateChildrenUri($this->getId());
    }

    public function delete()
    {
        foreach (self::getList(array('parent_id' => $this->getId())) as $item) {
            $item->delete();
        }

        foreach (App_Cms_Front_Data::getList($this->getPrimaryKeyWhere()) as $item) {
            $item->delete();
        }

        Ext_File::deleteDir($this->getFilePath());

        return parent::delete();
    }

    public function isChildren($_exceptId = null)
    {
        if (is_null($this->_isChildren)) {
            $list = self::getList(array('parent_id' => $this->getId()));

            if (is_null($_exceptId)) {
                $this->_isChildren = ($list);

            } else if ($list) {
                $this->_isChildren = false;

                foreach ($list as $item) {
                    if ($item->getId() != $_exceptId) {
                        $this->_isChildren = true;
                        break;
                    }
                }

            } else {
                $this->_isChildren = false;
            }
        }

        return $this->_isChildren;
    }

    public static function getMultiAncestors($_ids)
    {
        $result = array();

        foreach ($_ids as $id) {
            if (!in_array($id, $result)) {
                $result = array_merge($result, self::getAncestors($id));
            }
        }

        return $result;
    }

    public static function getAncestors($_id)
    {
        $result = array();
        $key = self::getPri();

        $entry = App_Db::get()->getEntry(App_Db::get()->getSelect(
            self::getTbl(),
            array($key, 'parent_id'),
            array($key => $_id)
        ));

        if ($entry) {
            $result[] = $entry[$key];

            if ($entry['parent_id']) {
                $result = array_merge(
                    $result,
                    self::getAncestors($entry['parent_id'])
                );
            }
        }

        return $result;
    }

    /**
     * @return App_Cms_Front_Controller
     */
    public function getController()
    {
        if (is_null($this->_controller)) {
            $this->_controller = $this->frontControllerId
                               ? App_Cms_Front_Controller::getById($this->frontControllerId)
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
     * @param App_Cms_Front_Controller $_controller
     * @param App_Cms_Front_Document $_document
     * @return App_Cms_Front_Document_Controller|false
     */
    public static function initController(App_Cms_Front_Controller $_controller, &$_document)
    {
        require_once $_controller->getFilename();

        $class = $_controller->getClassName();

        if (class_exists($class)) {
            return new $class($_document);
        }

        return false;
    }

    /**
     * @return App_Cms_Front_Template
     */
    public function getTemplate()
    {
        if (is_null($this->_template)) {
            $this->_template = $this->frontTemplateId
                             ? App_Cms_Front_Template::getById($this->frontTemplateId)
                             : false;
        }

        return $this->_template;
    }

    public static function checkUnique($_parentId, $_folder, $_exceptId = null)
    {
        $where = array(
            'parent_id' => $_parentId,
            'folder' => $_folder
        );

        if ($_exceptId) {
            $where[] = self::getPri() . ' != ' . App_Db::escape($_exceptId);
        }

        return 0 == count(self::getList($where, array('limit' => 1)));
    }
}
