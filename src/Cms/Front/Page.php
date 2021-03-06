<?php

namespace Core\Cms\Front;

abstract class Page extends \App\Cms\Page
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

        if (!empty($gIsUsers) && \App\Cms\User::get()) {
            $this->addSystem(\App\Cms\User::get()->getXml());
        }

        $this->addSystem(\App\Cms\Session::get()->getXml());

        return parent::getXml();
    }

    public function output($_createCache = true)
    {
        global $gCache, $gIsAdminMode;

        if (isset($_GET['xml']) && !empty($gIsAdminMode)) {
            header('Content-type: text/xml; charset=utf-8');

            echo \Core\Cms\Ext\Xml::getDocumentForXml(
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
