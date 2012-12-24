<?php

abstract class Core_Cms_Document_Controller_Common extends App_Cms_Document_Controller
{
    public function execute()
    {
        parent::execute();

        // Шаблон

        $template = $this->Document->getTemplate();
        if ($template->getFile()) {
            $this->setTemplate($template->getFile()->getPath());

        } else {
            $this->setTemplate(TEMPLATES . 'fo.xsl');
        }

        // Site navigation

        $navigationXml = '';
        $navigation = App_Cms_Document_Navigation::getList(
            array('is_published' => 1),
            null,
            array('name != "robots-sitemap"')
        );

        foreach ($navigation as $i) {
            $navigationXml .= App_Cms_Document_Navigation::getNavigationXml(
                $i->name,
                $i->type
            );
        }

        if ($navigationXml != '') {
            $this->addSystem('<navigation>' . $navigationXml . '</navigation>');
        }
    }
}
