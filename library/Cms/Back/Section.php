<?php

abstract class Core_Cms_Back_Section extends App_Model
{
    protected $_linkParams = array(
        'users' => 'App_Cms_Back_User_Has_Section'
    );

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addAttr('title', 'string');
        $this->addAttr('uri', 'string');
        $this->addAttr('description', 'string');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    public function getName()
    {
        return Ext_File::normalizeName($this->getTitle());
    }

    public static function checkUnique($_value, $_excludedId = null)
    {
        return parent::isUnique('uri', $_value, $_excludedId);
    }

    public static function compute()
    {
        global $g_section_start_url;

        $url = parse_url($_SERVER['REQUEST_URI']);
        $path = explode(
            '/',
            trim(str_replace($g_section_start_url, '', $url['path']), '/')
        );

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
        Ext_Xml::append($xml, Ext_Xml::notEmptyCdata('description', $this->description));

        return parent::getXml(null, $xml, $attrs);
    }
}
