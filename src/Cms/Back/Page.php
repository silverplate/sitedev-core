<?php

namespace Core\Cms\Back;

abstract class Page extends \App\Cms\Page
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

        if (\App\Cms\Back\Section::get()) {
            $this->setTitle(\App\Cms\Back\Section::get()->getTitle());
        }

        $this->addSystem($this->_getUserNavigationXml());
    }

    public function isAuthorized()
    {
        return (boolean) \App\Cms\Back\User::get();
    }

    public function isAllowed()
    {
        return $this->isAuthorized() && (
            (\App\Cms\Back\Section::get() && \App\Cms\Back\User::get()->isSection(\App\Cms\Back\Section::get()->getId())) ||
            $this->_url['path'] == \App\Cms\Back\Office::$uriStartsWith
        );
    }

    protected function _getUserNavigationXml()
    {
        $xml = '';

        if (\App\Cms\Back\User::get()) {
            foreach (\App\Cms\Back\User::get()->getSections() as $item) {
                $xml .= $item->getNavigationXml();
            }

            $xml = \Ext\Xml::notEmptyNode('navigation', $xml);
        }

        return $xml;
    }

    public function setUpdateStatus($_type, $_message = null)
    {
        $this->_updateStatus = array('type' => $_type, 'message' => $_message);
    }

    public function getXml()
    {
        global $gSiteTitle;

        $this->addSystem(\Ext\Xml::cdata('title', $gSiteTitle));

        if (\App\Cms\Back\User::get()) {
            $this->addSystem(\App\Cms\Back\User::get()->getXml());
        }

        $this->addSystem(\App\Cms\Session::get()->getXml(
            null,
            \App\Cms\Session::get()->getWorkmateXml()
        ));

        if ($this->_updateStatus) {
            $this->addContent(\Ext\Xml::notEmptyCdata(
                'update-status',
                $this->_updateStatus['message'],
                array('type' => $this->_updateStatus['type'])
            ));
        }

        return parent::getXml();
    }
}
