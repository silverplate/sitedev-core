<?php

abstract class Core_Cms_Back_Page extends App_Cms_Page
{
    protected $_updateStatus = array();

    public function __construct($_isAuthorize = true)
    {
        parent::__construct();

        if ($_isAuthorize) {
            if ($this->isAllowed())         $template = 'page.xsl';
            else if ($this->isAuthorized()) $template = '404.xsl';
            else                            $template = '403.xsl';

            $this->setTemplate(TEMPLATES . "back/$template");
        }

        $this->addSystem($this->_getUserNavigationXml());
    }

    public function isAuthorized()
    {
        return (boolean) App_Cms_Back_User::get();
    }

    public function isAllowed()
    {
        return $this->isAuthorized() && (
            (App_Cms_Back_Section::get() && App_Cms_Back_User::get()->isSection(App_Cms_Back_Section::get()->getId())) ||
            $this->_url['path'] == App_Cms_Back_Office::$uriStartsWith
        );
    }

    protected function _getUserNavigationXml()
    {
        $xml = '';

        if (App_Cms_Back_User::get()) {
            foreach (App_Cms_Back_User::get()->getSections() as $item) {
                $xml .= $item->getNavigationXml();
            }

            $xml = Ext_Xml::notEmptyNode('navigation', $xml);
        }

        return $xml;
    }

    public function setUpdateStatus($_type, $_message = null)
    {
        $this->_updateStatus = array('type' => $_type, 'message' => $_message);
    }

    public function getXml()
    {
        if (defined('SITE_TITLE') && SITE_TITLE) {
            $this->addSystem(Ext_Xml::cdata('title', SITE_TITLE));
        }

        if (App_Cms_Back_User::get()) {
            $this->addSystem(App_Cms_Back_User::get()->getXml());
        }

        $this->addSystem(App_Cms_Session::get()->getXml(
            null,
            App_Cms_Session::get()->getWorkmateXml()
        ));

        if ($this->_updateStatus) {
            $this->addContent(Ext_Xml::notEmptyCdata(
                'update-status',
                $this->_updateStatus['message'],
                array('type' => $this->_updateStatus['type'])
            ));
        }

        return parent::getXml();
    }
}
