<?php

abstract class Core_Cms_Front_Page extends App_Cms_Page
{
    protected $_isHidden;

    public function __construct()
    {
        global $gIsHidden;

        parent::__construct();

        $this->_isHidden = !empty($gIsHidden);

        if ($this->_isHidden) {
            $this->addSystemAttr('is-hidden');
        }
    }

    public function getXml()
    {
        global $gSiteTitle, $gIsUsers;

        $this->addSystem(\Ext\Xml::cdata('title', $gSiteTitle));

        if (!empty($gIsUsers) && App_Cms_User::get()) {
            $this->addSystem(App_Cms_User::get()->getXml());
        }

        $this->addSystem(App_Cms_Session::get()->getXml());

        return parent::getXml();
    }

    public function output($_createCache = true)
    {
        global $gCache, $gIsAdminMode;

        if (isset($_GET['xml']) && !empty($gIsAdminMode)) {
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
