<?php

require('../prepend.php');

$page = new App_Cms_Back_Page();
$page->setTitle($g_section->getTitle());

if ($page->isAuthorized()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_Front_Controller::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new App_Cms_Front_Controller();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = App_Cms_Ext_Form::load('form.xml');
        $form->fillWithObject($obj);

        if ($obj->id) {
            $form->content = $obj->getContent();

            if ($obj->isDocumentMain) {
                $form->typeId = 3;
            }
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

                if ($obj->typeId == 3) {
                    $obj->typeId = 1;
                    $obj->isDocumentMain = true;

                } else {
                    $obj->isDocumentMain = false;
                }

                if ($obj->checkUnique()) {
                    $obj->save();

                    App_Cms_Back_Log::logModule(
                        $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    if (
                        $form->isSubmited('update') ||
                        (!is_file($obj->getFilename()) && $form->content != '')
                    ) {
                        $obj->saveContent($form->content);
                    }

                    if ($obj->isDocumentMain) {
                        App_Db::get()->execute(
                            'UPDATE ' . $obj->getTable() .
                            ' SET is_document_main = 0' .
                            ' WHERE is_document_main = 1 AND ' .
                            $obj->getPrimaryKeyWhereNot()
                        );
                    }

                    App_Cms_Ext_Form::saveCookieStatus();

                    reload('?id=' . $obj->getId());

                } else {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->filename->setUpdateStatus(Ext_Form_Element::ERROR_EXIST);
                }
            }
        }
    }


    // Статус обработки формы

    $formStatusXml = '';

    if (!isset($form) || !$form->isSubmited()) {
        $formStatusXml = App_Cms_Ext_Form::getCookieStatusXml(
            empty($obj) ? 'Выполнено' : 'Данные контролера сохранены'
        );

        App_Cms_Ext_Form::clearCookieStatus();
    }


    // Внутренняя навигация

    $listXml = '';

    foreach (App_Cms_Front_Controller::getList() as $item) {
        $listXml .= $item->getBackOfficeXml();
    }

    $listXml = Ext_Xml::node('local-navigation', $listXml);


    // XML модуля

    if (isset($obj)) {
        $module = '<module type="simple" is-able-to-add="true"';

        if ($obj->getId()) {
            $module .= ' id="' . $obj->id . '">';
            $module .= Ext_Xml::cdata('title', $obj->getTitle());

        } else {
            $module .= ' is-new="true">';
            $module .= Ext_Xml::cdata('title', 'Добавление');
        }

        $module .= $formStatusXml;
        $module .= $form->getXml();
        $module .= $listXml;
        $module .= '</module>';

        $page->addContent($module);

    } else {
        $about = $g_section->description
               ? '<p class="first">' . $g_section->description . '</p>'
               : '';

        $page->addContent(Ext_Xml::node(
            'module',
            $formStatusXml .
            $listXml .
            Ext_Xml::notEmptyNode('content', Ext_Xml::notEmptyCdata('html', $about)),
            array('type' => 'simple', 'is-able-to-add' => 'true')
        ));
    }
}

$page->output();
