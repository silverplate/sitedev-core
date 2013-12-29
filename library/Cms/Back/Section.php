<?php

abstract class Core_Cms_Back_Section extends App_ActiveRecord
{
    protected $_linkParams = array(
        'users' => 'App_Cms_Back_User_Has_Section'
    );

    /**
     * @var App_Cms_Back_Section|false
     */
    protected static $_current;

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
        $this->addAttr('title', 'string');
        $this->addAttr('uri', 'string');
        $this->addAttr('description', 'string');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    public function getName()
    {
        return \Ext\File::normalizeName($this->getTitle());
    }

    public function checkUnique()
    {
        return self::isUnique(
            'uri',
            $this->uri,
            $this->id ? $this->id : null
        );
    }

    /**
     * @return App_Cms_Back_Section|false
     */
    public static function get()
    {
        if (!isset(self::$_current)) {
            self::$_current = self::compute();
        }

        return self::$_current;
    }

    /**
     * @return App_Cms_Back_Section|false
     */
    public static function compute()
    {
        $url = \Ext\File::parseUrl();

        $path = explode('/', trim(str_replace(
            App_Cms_Back_Office::$uriStartsWith,
            '',
            $url['path']
        ), '/'));

        return self::getBy('uri', $path[0]);
    }

    public function getUri()
    {
        return "/cms/{$this->uri}/";
    }

    public function getNavigationXml($_xml = array(), $_attrs = array())
    {
        $attrs = $_attrs;
        $attrs['uri'] = $this->getUri();

        $xml = $_xml;
        \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata('description', $this->description));

        return parent::getXml(null, $xml, $attrs);
    }
}
