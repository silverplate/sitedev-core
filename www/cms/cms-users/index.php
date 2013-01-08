<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_Back_User::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new App_Cms_Back_User();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = App_Cms_Ext_Form::load('form.xml');
        $form->fillWithObject($obj);

        foreach (App_Cms_Back_Section::getList() as $item) {
            $form->sections->addOption($item->id, $item->getTitle());
        }

        if ($obj->id) {
            $form->sections->setValue($obj->getLinkIds('sections'));
            $form->passwd = '';

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

                    if ($obj->ipRestriction) {
                        $obj->ipRestriction = implode(
                            "\n",
                            Ext_String::split($obj->ipRestriction)
                        );
                    }

                    $obj->save();

                    App_Cms_Back_Log::logModule(
                        $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    $obj->updateLinks('sections', $form->sections->getValue());

                    if (
                        $form->passwd->getValue() &&
                        App_Cms_Session::get()->getUserId() != $obj->id
                    ) {
                        App_Cms_Session::clean($obj->id);
                    }

                    App_Cms_Ext_Form::saveCookieStatus();
                    reload('?id=' . $obj->id);

                } else {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->login->setUpdateStatus(Ext_Form_Element::ERROR_EXIST);
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

    $listXml = '';

    foreach (App_Cms_Back_User::getList() as $item) {
        $listXml .= $item->getBackOfficeXml();
    }

    $listXml = Ext_Xml::node(
        'local-navigation',
        $listXml,
        array('is-sortable' => 1)
    );


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
