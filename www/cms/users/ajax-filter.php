<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$filter = obj_get_filter(true);
$resultItems = obj_filter($filter);

if (!$resultItems['items'] && $resultItems['total'] > 0 && $filter['page'] != 1) {
    $filter['page'] = 1;
    $resultItems = obj_filter($filter);
}

$page = new App_Cms_Page();
$page->setRootName('http-request');
$page->setRootAttr('type', 'filter');

if ($filter['selected_id']) {
    $page->setRootAttr('selected-id', $filter['selected_id']);
}

$page->setTemplate(TEMPLATES . 'back/http-requests.xsl');

if ($resultItems['items']) {
    foreach ($resultItems['items'] as $item) {
        $page->addContent($item->getBackOfficeXml());
    }

	$page->addContent(Ext_Xml::node('list-navigation', null, array(
        'page' => $filter['page'],
        'per-page' => $filter['per_page'],
        'total' => $resultItems['total']
    )));
}

header('Content-type: text/html; charset=utf-8');
$page->output();
