<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_User::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new App_Cms_User();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = App_Cms_Ext_Form::load(dirname(__FILE__) . '/form.xml');
        $form->fillWithObject($obj);

        if ($obj->id) {
            if ($obj->statusId != 1) {
                $form->statusId = 0;
            }

        } else {
            $form->getElement('passwd')->isRequired(true);
        }

        $form->run();

        if ($form->isSubmited() && $form->isSuccess()) {
            if ($form->isSubmited('delete')) {
                $obj->delete();

                App_Cms_Back_Log::logModule(
                    App_Cms_Back_Log::ACT_DELETE,
                    $obj->id,
                    $obj->getTitle()
                );

                App_Cms_Ext_Form::saveCookieStatus();
                redirect($page->getUrl('path'));

            } else {
                $obj->fillWithData($form->toArray());

                if ($obj->checkUnique()) {
                    $password = $form->passwd->getValue('passwd');

                    if ($password) {
                        $obj->setPassword($password);
                    }

                    if ($obj->statusId != 0 && $obj->statusId != 1) {
                        $obj->statusId = 0;
                    }

                    $obj->save();

                    App_Cms_Back_Log::logModule(
                        $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    App_Cms_Ext_Form::saveCookieStatus();
                    reload('?id=' . $obj->id);

                } else {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->email->setUpdateStatus(Ext_Form_Element::ERROR_EXIST);
                }
            }
        }
    }


    // Статус обработки формы

    $formStatusXml = '';

    if (!isset($form) || !$form->isSubmited()) {
        $formStatusXml = App_Cms_Ext_Form::getCookieStatusXml(
            empty($obj) ? 'Выполнено' : 'Данные сохранены'
        );

        App_Cms_Ext_Form::clearCookieStatus();
    }


    // Внутренняя навигация

    $filter = objGetFilter();
    $filterAttrs = array('type' => 'filter');
    $listXml = '';

    foreach (array('open', 'name', 'email') as $name) {
        if (!empty($filter['is_' . $name])) {
            $filterAttrs["is-$name"] = 1;

            if (!empty($filter[$name])) {
                $listXml .= Ext_Xml::cdata("filter-$name", $filter[$name]);
            }
        }
    }

    $listXml = Ext_Xml::node('local-navigation', $listXml, $filterAttrs);


    // XML модуля

    $xml = $listXml . $formStatusXml;
    $attrs = array('type' => 'simple', 'is-able-to-add' => 'true');

    if (empty($obj)) {
        if (App_Cms_Back_Section::get()->description) {
            $xml .= Ext_Xml::notEmptyNode('content', Ext_Xml::cdata(
                'html',
                '<p class="first">' . App_Cms_Back_Section::get()->description . '</p>'
            ));
        }

    } else if ($obj->getId()) {
        $attrs['id'] = $obj->id;
        $xml .= Ext_Xml::cdata('title', $obj->getTitle());
        $xml .= $form->getXml();

    } else {
        $attrs['is-new'] = 1;
        $xml .= Ext_Xml::cdata('title', 'Добавление');
        $xml .= $form->getXml();
    }

    $page->addContent(Ext_Xml::node('module', $xml, $attrs));
}

$page->output();
