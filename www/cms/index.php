<?php

require_once 'prepend.php';

$page = new \App\Cms\Back\Page();
$page->setTitle('Система управления');

if ($page->isAllowed()) {
    $page->setTemplate(TEMPLATES . 'back/home.xsl');
    $page->addContent('<sections-on-home-page />');
}

$page->output();
