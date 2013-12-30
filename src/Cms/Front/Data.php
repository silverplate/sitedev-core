<?php

namespace Core\Cms\Front;

abstract class Data extends \App\ActiveRecord
{
    /**
     * @var \App\Cms\Front\Controller
     */
    protected $_controller;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addForeign(\App\Cms\Front\Document::createInstance());
        $this->addForeign(\App\Cms\Front\Controller::createInstance());
        $this->addForeign(\App\Cms\Front\Data\ContentType::createInstance());
        $this->addAttr('auth_status_id', 'integer');
        $this->addAttr('tag', 'string');
        $this->addAttr('title', 'string');
        $this->addAttr('content', 'string');
        $this->addAttr('apply_type_id', 'integer');
        $this->addAttr('is_mount', 'boolean');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    public static function getApplyTypes()
    {
        return array(
            1 => 'На&nbsp;эту страницу',
            2 => 'На&nbsp;вложенные',
            3 => 'На&nbsp;эту и&nbsp;вложенные'
        );
    }

    public function checkApplyType()
    {
        if (!key_exists((int) $this->applyTypeId, self::getApplyTypes())) {
            $this->applyTypeId = 1;
        }
    }

    public function create()
    {
        $this->checkApplyType();
        return parent::create();
    }

    public function update()
    {
        $this->checkApplyType();
        return parent::update();
    }

    public function getParsedContent($_content)
    {
        switch ($this->frontDataContentTypeId) {
            case 'integer': return (integer) $_content;
            case 'float':   return (float) $_content;
            default:        return \Ext\Xml::decodeCdata($_content);
        }
    }

    public function getTypeId()
    {
        return $this->frontDataContentTypeId;
    }

    public function setTypeId($_id)
    {
        $this->frontDataContentTypeId = $_id;
    }

    public function getXml($_node = null, $_xml = null, $_attrs = null)
    {
        global $gIsUsers;

        // Name

        $node = $_node ? $_node : 'document-data';


        // @

        $attrs = empty($_attrs) ? array() : $_attrs;

        $attrs['type-id'] = $this->getTypeId();
        $attrs['tag'] = $this->tag;

        if ($this->isPublished) {
            $attrs['is-published'] = 1;
        }

        if ($this->isMount) {
            $attrs['is-mount'] = 1;
        }


        // XML

//         $xml = array();

//         \Ext\Xml::append(
//             $xml,
//             \Ext\Xml::notEmptyNode('additional', $_additionalXml)
//         );

        if (empty($_xml))         $xml = array();
        else if (is_array($_xml)) $xml = $_xml;
        else                      $xml = array($_xml);

        if ($this->getController()) {
            \Ext\Xml::append(
                $xml,
                \Ext\Xml::cdata('controller', $this->getController()->getTitle())
            );
        }

        \Ext\Xml::append(
            $xml,
            \Ext\Xml::notEmptyCdata('content', $this->content)
        );

        if (
            !empty($gIsUsers) &&
            $this->authStatusId != \App\Cms\User::AUTH_GROUP_ALL &&
            \App\Cms\User::getAuthGroupTitle($this->authStatusId)
        ) {
            \Ext\Xml::append($xml, \Ext\Xml::cdata(
                'auth-group',
                \App\Cms\User::getAuthGroupTitle($this->authStatusId)
            ));
        }

        return parent::getXml($node, $xml, $attrs);
    }

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
        return $this->getController() ? $this->getController()->getFilename() : false;
    }

    /**
     * @param \App\Cms\Front\Controller $_controller
     * @param \App\Cms\Front\Data $_data
     * @param \App\Cms\Front\Document $_document
     * @return \App\Cms\Front\Data\Controller
     */
    public static function initController($_controller, $_data, $_document)
    {
        require_once $_controller->getFilename();

        $class = $_controller->getClassName();
        return new $class($_data, $_document);
    }

    /**
     * @param \App\ActiveRecord $_parent
     * @return array|false
     */
    public function proceedContent(\App\ActiveRecord $_parent)
    {
        switch ($this->getTypeId()) {
            case 'image': return $this->proceedImage();
            case 'xml':   return $this->proceedXml($_parent);
        }

        return false;
    }

    /**
     * @return array
     */
    public function proceedImage()
    {
        if (strpos($this->content, '://') !== true) {
            $path = DOCUMENT_ROOT . ltrim($this->content, '/');

            if (is_file($path)) {
                $file = \App\Cms\Ext\Image::factory($path);

                if ($file) {
                    return array('type' => 'xml', 'content' => $file->getXml());
                }
            }
        }

        return false;
    }

    /**
     * @param \App\ActiveRecord $_parent
     * @return array
     */
    public function proceedXml(\App\ActiveRecord $_parent)
    {
        return array('content' => Core_Cms_Ext_Xml::applyFiles(
            $this->content,
            $_parent->getFiles()
        ));
    }
}
