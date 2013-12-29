<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {
    $page->addContent(\Ext\Xml::node(
        'module',
        App_Cms_Back_Log::getCmsNavFilter()->getXml(),
        array('type' => 'simple')
    ));
}

$page->output();
