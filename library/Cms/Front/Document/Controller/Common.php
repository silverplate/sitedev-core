<?php

abstract class Core_Cms_Front_Document_Controller_Common
extends App_Cms_Front_Document_Controller
{
    public function execute()
    {
        parent::execute();

        $this->setTemplate($this->_document->getTemplate()->getFile()->getPath());
        $this->_computeNavigationXml();
    }

    protected function _computeNavigationXml()
    {
        $xml = '';
        $navigation = App_Cms_Front_Navigation::getList(array(
            'is_published' => 1,
            'name != "robots-sitemap"'
        ));

        foreach ($navigation as $i) {
            $xml .= App_Cms_Front_Navigation::getNavigationXml($i->name, $i->type);
        }

        $this->addSystem(Ext_Xml::notEmptyNode('navigation', $xml));
    }
}
