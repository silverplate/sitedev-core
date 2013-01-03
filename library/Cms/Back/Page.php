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
        global $g_user;
        return !empty($g_user);
    }

    public function isAllowed()
    {
        global $g_user, $g_section, $g_section_start_url;

        return $this->isAuthorized() && (
            (!empty($g_section) && $g_user->isSection($g_section->getId())) ||
            $this->_url['path'] == $g_section_start_url
        );
    }

    protected function _getUserNavigationXml()
    {
        global $g_user;

        $xml = '';

        if (!empty($g_user)) {
            foreach ($g_user->getSections() as $item) {
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
        global $g_user;

        if (defined('SITE_TITLE') && SITE_TITLE) {
            $this->addSystem(Ext_Xml::cdata('title', SITE_TITLE));
        }

        if (!empty($g_user)) {
            $this->addSystem($g_user->getXml());
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
