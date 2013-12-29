<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_Back_Section::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new App_Cms_Back_Section();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = App_Cms_Ext_Form::load(dirname(__FILE__) . '/form.xml');
        $form->fillWithObject($obj);

        foreach (App_Cms_Back_User::getList() as $item) {
            $form->users->addOption($item->id, $item->getTitle());
        }

        if ($obj->id) {
            $form->users->setValue($obj->getLinkIds('users'));
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
                    $obj->save();

                    App_Cms_Back_Log::logModule(
                        $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    $obj->updateLinks('users', $form->users->getValue());

                    App_Cms_Ext_Form::saveCookieStatus();
                    reload('?id=' . $obj->id);

                } else {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->uri->setUpdateStatus(\Ext\Form\Element::ERROR_EXIST);
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

    $filterXml = '';

    foreach (App_Cms_Back_Section::getList() as $item) {
        $filterXml .= $item->getBackOfficeXml();
    }

    $filterXml = \Ext\Xml::node(
        'local-navigation',
        $filterXml,
        array('is-sortable' => 1)
    );


    // XML модуля

    $xml = $filterXml . $formStatusXml;
    $attrs = array('type' => 'simple', 'is-able-to-add' => 'true');

    if (empty($obj)) {
        if (App_Cms_Back_Section::get()->description) {
            $xml .= \Ext\Xml::notEmptyNode('content', \Ext\Xml::cdata(
                'html',
                '<p class="first">' . App_Cms_Back_Section::get()->description . '</p>'
            ));
        }

    } else if ($obj->getId()) {
        $attrs['id'] = $obj->id;
        $xml .= \Ext\Xml::cdata('title', $obj->getTitle());
        $xml .= $form->getXml();

    } else {
        $attrs['is-new'] = 1;
        $xml .= \Ext\Xml::cdata('title', 'Добавление');
        $xml .= $form->getXml();
    }

    $page->addContent(\Ext\Xml::node('module', $xml, $attrs));
}

$page->output();
