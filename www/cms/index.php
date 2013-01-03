<?php

require 'prepend.php';

$page = new App_Cms_Back_Page();
$page->setTitle('Система управления');

if ($page->isAllowed()) {
    $page->setTemplate(TEMPLATES . 'back/home.xsl');
    $page->addContent('<sections-on-home-page />');
}

$page->output();
