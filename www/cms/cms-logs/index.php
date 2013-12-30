<?php

require_once '../prepend.php';

$page = new \App\Cms\Back\Page();

if ($page->isAllowed()) {
    $page->addContent(\Ext\Xml::node(
        'module',
        \App\Cms\Back\Log::getCmsNavFilter()->getXml(),
        array('type' => 'simple')
    ));
}

$page->output();
