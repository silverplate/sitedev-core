<?php

namespace Core\Controller;

abstract class Common extends \App\Cms\Front\Document\Controller
{
    public function execute()
    {
        parent::execute();

        $this->setTemplate(
            $this->_document->getTemplate()->getTemplateFile()->getPath()
        );
        $this->_computeNavigationXml();
    }

    protected function _computeNavigationXml()
    {
        $xml = '';
        $navigation = \App\Cms\Front\Navigation::getList(array(
            'is_published' => 1,
            'name != "robots-sitemap"'
        ));

        foreach ($navigation as $i) {
            $xml .= \App\Cms\Front\Navigation::getNavigationXml(
                $i->name,
                $i->type
            );
        }

        $this->addSystem(\Ext\Xml::notEmptyNode('navigation', $xml));
    }
}
