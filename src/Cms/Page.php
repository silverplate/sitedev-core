<?php

namespace Core\Cms;

abstract class Page
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
        global $gHost;

        $this->_url = \Ext\File::parseUrl();
        $this->_url['host'] = $gHost;
    }

    public function getUrlXml()
    {
        $url = $this->getUrl();
        unset($url['request_uri']);

        return \Ext\Xml::cdata('url', $this->getUrl('request_uri'), $url);
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
            array_key_exists('xml', $_GET) ||
            (array_key_exists('post-xml', $_GET) && $_POST)
        ) {
            header('Content-type: text/xml; charset=utf-8');

            echo \App\Cms\Ext\Xml::getDocumentForXml(
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

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyNode(
            'content',
            $this->_content
        ));

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyCdata('title', $this->getTitle()));
        \Ext\Xml::append($xml, $this->getUrlXml());
        \Ext\Xml::append($xml, \Ext\Date::getXml(time()));

        \Ext\Xml::append($xml, \Ext\Xml::notEmptyNode(
            'system',
            $this->_system,
            $this->_systemAttrs
        ));

        return \Ext\Xml::node(
            $this->getRootName(),
            $xml,
            $this->_rootAttrs
        );
    }

    public function getHtml()
    {
        $proc = new \XSLTProcessor();
        $proc->importStylesheet(\Ext\Xml\Dom::load($this->_template));

        return $proc->transformToXml(\Ext\Xml\Dom::get(\App\Cms\Ext\Xml::getDocumentForXml(
            $this->getXml(),
            $this->getRootName()
        )));
    }
}
