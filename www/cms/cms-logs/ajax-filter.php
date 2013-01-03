<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$filter = bo_log_get_filter(true);
$resultItems = bo_log_filter($filter);

if (!$resultItems['items'] && $resultItems['total'] > 0 && $filter['page'] != 1) {
	$filter['page'] = 1;
	$resultItems = bo_log_filter($filter);
}

$page = new App_Cms_Page();
$page->setRootName('http-request');
$page->setRootAttr('type', 'back-logs');
$page->setTemplate(TEMPLATES . 'back/http-requests.xsl');

if ($resultItems['items']) {
	$users = App_Cms_Back_User::getList();
	$sections = App_Cms_Back_Section::getList();
	$actions = App_Cms_Back_Log::getActions();

	foreach ($resultItems['items'] as $item) {
		$xml = array();

		if ($item->backUserId && isset($users[$item->backUserId])) {
			$xml['user'] = $users[$item->backUserId]->GetTitle();

		} else if ($item->userName) {
			$xml['user'] = $item->userName;
		}

		if ($item->backSectionId && isset($sections[$item->backSectionId])) {
			$xml['section'] = $sections[$item->backSectionId]->getTitle();

		} else if ($item->sectionName) {
			$xml['section'] = $item->sectionName;
		}

		if (isset($actions[$item->actionId])) {
			$xml['action'] = $actions[$item->actionId];
		}

		$appendXml = '';

		foreach ($xml as $name => $value) {
		    $appendXml .= Ext_Xml::cdata($name, $value);
		}

		$page->addContent($item->getBackOfficeXml($appendXml));
	}

	$page->addContent(Ext_Xml::node('list-navigation', null, array(
        'page' => $filter['page'],
        'per-page' => $filter['per_page'],
        'total' => $resultItems['total']
    )));
}

header('Content-type: text/html; charset=utf-8');
$page->output();
