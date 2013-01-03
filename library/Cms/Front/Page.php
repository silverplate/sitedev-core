<?php

abstract class Core_Cms_Front_Page extends App_Cms_Page
{
    protected $_isHidden;

    public function __construct()
    {
        parent::__construct();

        $this->_isHidden = defined('IS_HIDDEN') && IS_HIDDEN;

        if ($this->_isHidden) {
            $this->addSystemAttr('is-hidden');
        }
    }

    public function getXml()
    {
        if (defined('SITE_TITLE') && SITE_TITLE) {
            $this->addSystem(Ext_Xml::cdata('title', SITE_TITLE));
        }

        if (defined('IS_USERS') && IS_USERS && App_Cms_User::get()) {
            $this->addSystem(App_Cms_User::get()->getXml());
        }

        $this->addSystem(App_Cms_Session::get()->getXml());

        return parent::getXml();
    }

    public function output($_createCache = true)
    {
        global $gCache;

        if (isset($_GET['xml']) && defined('IS_ADMIN_MODE') && IS_ADMIN_MODE) {
            header('Content-type: text/xml; charset=utf-8');

            echo Core_Cms_Ext_Xml::getDocumentForXml(
                $this->getXml(),
                $this->getRootName()
            );

        } else if ($this->_template) {
            $content = $this->getHtml();
            echo $content;

            if ($gCache && $gCache->isAvailable() && $_createCache) {
                $gCache->set($content);
            }

        } else {
            documentNotFound();
        }
    }
}
