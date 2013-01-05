<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$page = new App_Cms_Back_Page();

if ($page->isAuthorized()) {
    $filter = boLogGetFilter();

    $xml = '';
    $xmlAttrs = array(
        'type'     => 'content-filter',
        'is-date'  => 1,
        'today'    => date('Y-m-d'),
        'week'     => date('Y-m-d', time() - 60 * 60 * 24 * 7),
        'month'    => date('Y-m-d', time() - 60 * 60 * 24 * 31),
        'all-from' => date('Y-m-d', time() - 60 * 60 * 24 * 365 * 5),
        'all-till' => date('Y-m-d', time() + 60 * 60 * 24 * 365 * 5),
        'from'     => date('Y-m-d', $filter['from_date']),
        'till'     => date('Y-m-d', $filter['till_date'])
    );

    if (!empty($filter['is_open'])) {
        $xmlAttrs['is_open'] = 1;
    }


    // Пользователи

    $lAttrs = array('type' => 'multiple', 'name' => 'users');
    if ($filter['is_users']) $lAttrs['is-selected'] = 1;

    $lXml = Ext_Xml::cdata('title', 'Пользователь');

    foreach (App_Cms_Back_User::getList() as $item) {
        $attrs = array('value' => $item->id);

        if ($filter['users'] && in_array($item->id, $filter['users'])) {
            $attrs['is-selected'] = 1;
        }

        $lXml .= Ext_Xml::cdata('item', $item->getTitle(), $attrs);
    }

    $xml .= Ext_Xml::node('filter-param', $lXml, $lAttrs);


    // Разделы

    $lAttrs = array('type' => 'multiple', 'name' => 'sections');
    if ($filter['is_sections']) $lAttrs['is-selected'] = 1;

    $lXml = Ext_Xml::cdata('title', 'Раздел');

    foreach (App_Cms_Back_Section::getList() as $item) {
        $attrs = array('value' => $item->id);

        if ($filter['sections'] && in_array($item->id, $filter['sections'])) {
            $attrs['is-selected'] = 1;
        }

        $lXml .= Ext_Xml::cdata('item', $item->getTitle(), $attrs);
    }

    $xml .= Ext_Xml::node('filter-param', $lXml, $lAttrs);


    // Действия

    $lAttrs = array('type' => 'multiple', 'name' => 'actions');
    if ($filter['is_actions']) $lAttrs['is-selected'] = 1;

    $lXml = Ext_Xml::cdata('title', 'Действие');

    foreach (App_Cms_Back_Log::getActions() as $id => $title) {
        $attrs = array('value' => $id);

        if ($filter['actions'] && in_array($id, $filter['actions'])) {
            $attrs['is-selected'] = 1;
        }

        $lXml .= Ext_Xml::cdata('item', $title, $attrs);
    }

    $xml .= Ext_Xml::node('filter-param', $lXml, $lAttrs);


    $page->addContent(Ext_Xml::node(
        'module',
        Ext_Xml::node('local-navigation', $xml, $xmlAttrs),
        array('type' => 'simple')
    ));
}

$page->output();
