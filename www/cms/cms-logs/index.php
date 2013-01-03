<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$page = new App_Cms_Back_Page();
$page->SetTitle($g_section->GetTitle());

if ($page->IsAuthorized()) {
    $filter = bo_log_get_filter();

    $listXml  = '<local-navigation type="content_filter" is-date="true"';
    $listXml .= ' today="' . date('Y-m-d') . '" week="' . date('Y-m-d', strtotime('-1 week')) . '" month="' . date('Y-m-d', strtotime('-1 month')) . '" all_from="' . date('Y-m-d', strtotime('-5 years')) . '" all_till="' . date('Y-m-d', strtotime('+5 years')) . '"';
    $listXml .= ' from="' . date('Y-m-d', $filter['from_date']) . '" till="' . date('Y-m-d', $filter['till_date']) . '"';
    foreach (array('open') as $item) {
        if ($filter['is_' . $item]) $listXml .= ' is-' . $item . '="true"';
    }
    $listXml .= '>';

    $listXml .= '<filter-param type="multiple" name="users"';
    if ($filter['is_users']) $listXml .= ' is-selected="true"';
    $listXml .= '><title><![CDATA[Пользователь]]></title>';
    foreach (App_Cms_Back_User::GetList() as $item) {
        $listXml .= '<item value="' . $item->GetId() . '"';
        if (is_array($filter['users']) && in_array($item->GetId(), $filter['users'])) {
            $listXml .= ' is-selected="true"';
        }
        $listXml .= '><![CDATA[' . $item->GetTitle() . ']]></item>';
    }
    $listXml .= '</filter-param>';

    $listXml .= '<filter-param type="multiple" name="sections"';
    if ($filter['is_sections']) $listXml .= ' is-selected="true"';
    $listXml .= '><title><![CDATA[Раздел]]></title>';
    foreach (App_Cms_Back_Log::GetList() as $item) {
        $listXml .= '<item value="' . $item->GetId() . '"';
        if (is_array($filter['sections']) && in_array($item->GetId(), $filter['sections'])) {
            $listXml .= ' is-selected="true"';
        }
        $listXml .= '><![CDATA[' . $item->GetTitle() . ']]></item>';
    }
    $listXml .= '</filter-param>';

    $listXml .= '<filter-param type="multiple" name="actions"';
    if ($filter['is_actions']) $listXml .= ' is-selected="true"';
    $listXml .= '><title><![CDATA[Действие]]></title>';
    foreach (App_Cms_Back_Log::GetActions() as $id => $title) {
        $listXml .= '<item value="' . $id . '"';
        if (is_array($filter['actions']) && in_array($id, $filter['actions'])) {
            $listXml .= ' is-selected="true"';
        }
        $listXml .= '><![CDATA[' . $title . ']]></item>';
    }
    $listXml .= '</filter-param>';
    $listXml .= '</local-navigation>';

    $page->AddContent('<module type="simple">' . $listXml . '</module>');
}

$page->Output();
