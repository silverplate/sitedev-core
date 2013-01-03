<?php

abstract class Core_Cms_Page
{
    protected $_title;
    protected $_template;
    protected $_url = array();
    protected $_content = array();
    protected $_system = array();
    protected $_systemAttrs = array();
    protected $_rootName;
    protected $_rootAttrs = array();

    public function __construct()
    {
        $this->_computeUrl();
    }

    public function setTitle($_value)
    {
        $this->_title = $_value;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTemplate($_file)
    {
        $this->_template = $_file;
    }

    public function setRootName($_name)
    {
        $this->_rootName = $_name;
    }

    public function getRootName()
    {
        return $this->_rootName ? $this->_rootName : 'page';
    }

    public function setRootAttr($_name, $_value)
    {
        $this->_rootAttrs[$_name] = $_value;
    }

    protected function _computeUrl()
    {
        $this->_url = parse_url($_SERVER['REQUEST_URI']);
        $this->_url['request_uri'] = $_SERVER['REQUEST_URI'];
        $this->_url['host'] = $_SERVER['HTTP_HOST'];

        if (!isset($this->_url['query'])) {
            $this->_url['query'] = '';
        }
    }

    public function getUrlXml()
    {
        $url = $this->_url;
        unset($url['request_uri']);

        return Ext_Xml::cdata('url', $this->_url['request_uri'], $url);
    }

    public function getUrl($_name = null)
    {
        return $_name ? $this->_url[$_name] : $this->_url;
    }

    public function addSystem($_source)
    {
        if ($_source) {
            $this->_system[] = $_source;
        }
    }

    public function addSystemAttr($_name, $_value = 'true')
    {
        $this->_systemAttrs[$_name] = $_value;
    }

    public function addContent($_source)
    {
        if ($_source) {
            $this->_content[] = $_source;
        }
    }

    public function getContent()
    {
        return $this->_content;
    }

    public function setContent(array $_content)
    {
        $this->_content = $_content;
    }

    public function output()
    {
        if (
            key_exists('xml', $_GET) ||
            (key_exists('post-xml', $_GET) && $_POST)
        ) {
            header('Content-type: text/xml; charset=utf-8');

            echo Core_Cms_Ext_Xml::getDocumentForXml(
                $this->getXml(),
                $this->getRootName()
            );

        } else {
            echo $this->getHtml();
        }
    }

    public function getXml()
    {
        $xml = '';

        Ext_Xml::append($xml, Ext_Xml::notEmptyNode(
            'content',
            $this->_content
        ));

        Ext_Xml::append($xml, Ext_Xml::notEmptyCdata('title', $this->getTitle()));
        Ext_Xml::append($xml, $this->getUrlXml());
        Ext_Xml::append($xml, Ext_Date::getXml(time()));

        Ext_Xml::append($xml, Ext_Xml::notEmptyNode(
            'system',
            $this->_system,
            $this->_systemAttrs
        ));

        return Ext_Xml::node(
            $this->getRootName(),
            $xml,
            $this->_rootAttrs
        );
    }

    public function getHtml()
    {
        $proc = new XSLTProcessor();
        $proc->importStylesheet(Ext_Dom::load($this->_template));

        return $proc->transformToXml(Ext_Dom::get(Ext_Xml::getDocumentForXml(
            $this->getXml(),
            $this->getRootName()
        )));
    }
}
