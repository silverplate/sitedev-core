<?php

require_once '../prepend.php';

global $gCache, $gIsUsers;

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_Front_Document::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new App_Cms_Front_Document();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = App_Cms_Ext_Form::load(dirname(__FILE__) . '/document-form.xml');

        // Добавление документа в навигацию
        foreach (
            App_Cms_Front_Navigation::getList(array('is_published' => 1)) as
            $item
        ) {
            $form->navigations->addOption($item->getId(), $item->getTitle());
        }

        // Контроллер
        $used = Ext_Db::get()->getList(Ext_Db::get()->getSelect(
            $obj->getTable(),
            App_Cms_Front_Controller::getPri(),
            $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
        ));

        foreach (App_Cms_Front_Controller::getList(array('type_id' => 1)) as $item) {
            if (
                $item->id == $obj->frontControllerId || (
                    $item->isPublished &&
                    ($item->isMultiple || !in_array($item->id, $used))
                )
            ) {
                $form->frontControllerId->addOption($item->id, $item->getTitle());

                // По умолчанию
                if ($item->isDocumentMain && !$form->frontControllerId->getValue()) {
                    $form->frontControllerId = $item->id;
                }
            }
        }

        // Шаблон
        $used = Ext_Db::get()->getList(Ext_Db::get()->getSelect(
            $obj->getTable(),
            App_Cms_Front_Template::getPri(),
            $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
        ));

        foreach (
            App_Cms_Front_Template::getList(null, array('order' => 'is_document_main DESC, title')) as
            $item
        ) {
            if (
                $item->id == $obj->frontTemplateId || (
                    $item->isPublished &&
                    ($item->isMultiple || !in_array($item->id, $used))
                )
            ) {
                $form->frontTemplateId->addOption($item->id, $item->getTitle());

                // По умолчанию
                if ($item->isDocumentMain && !$form->frontTemplateId->getValue()) {
                    $form->frontTemplateId = $item->id;
                }
            }
        }

        // Доступ для групп пользователей
        if (!empty($gIsUsers)) {
            $form->getGroup('system')->addElement($form->createElement(
                'auth_status_id',
                'chooser',
                'Страница доступна'
            ));

            foreach (App_Cms_User::getAuthGroups() as $id => $params) {
                $form->authStatusId->addOption(
                    $id,
                    Ext_String::toLower($params['title1'])
                );
            }
        }

        // Данные
        $form->fillWithObject($obj);

        if ($obj->id) {
            $form->getGroup('content')->addAdditionalXml(Ext_Xml::node('document-data'));
            $form->navigations = $obj->getLinkIds('navigations');

            foreach ($obj->getFiles() as $file) {
                $form->files->addAdditionalXml($file->getXml());
            }

        } else {
            $form->deleteGroup('content');
        }

        $form->run();

        // Обработка формы
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

                if (!$obj->checkFolder() || !$obj->checkRoot()) {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->folder->setUpdateStatus(Ext_Form_Element::ERROR_SPELLING);

                } else if (!$obj->checkUnique()) {
                    $form->setUpdateStatus(App_Cms_Ext_Form::ERROR);
                    $form->folder->setUpdateStatus(Ext_Form_Element::ERROR_EXIST);

                } else {
                    $obj->save();

                    App_Cms_Back_Log::logModule(
                        $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    if ($form->isSubmited('update')) {
                        foreach (App_Cms_Front_Data::getList(array(
                            $obj->getPrimaryKeyWhere(),
                            'is_mount' => 1
                        )) as $data) {
                            $key = 'document_data_form_ele_' . $data->id;

                            if (key_exists($key, $_POST)) {
                                $data->updateAttr(
                                    'content',
                                    $data->getParsedContent($_POST[$key])
                                );

                                App_Cms_Back_Log::LogModule(
                                    App_Cms_Back_Log::ACT_MODIFY,
                                    $data->id,
                                    'Блоки данных, документ ' . $obj->id
                                );
                            }
                        }
                    }

                    if ($form->files->getValue()) {
                        foreach ($form->files->getValue() as $file) {
                            $obj->uploadFile($file['name'], $file['tmp_name']);
                        }

                        $obj->cleanFileCache();
                    }

                    $obj->updateLinks('navigations', $form->navigations->getValue());

                    App_Cms_Ext_Form::saveCookieStatus();

                    reload('?id=' . $obj->id);
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


    // XML модуля

    $xml = $formStatusXml;
    $attrs = array(
        'type' => 'tree',
        'is-able-to-add' => 'true',
        'name' => App_Cms_Back_Section::get()->getName()
    );

    if (empty($obj)) {
        if (App_Cms_Back_Section::get()->description) {
            $xml .= Ext_Xml::notEmptyNode('content', Ext_Xml::cdata(
                'html',
                '<p class="first">' . App_Cms_Back_Section::get()->description . '</p>'
            ));
        }

    } else if ($obj->getId()) {
        $attrs['id'] = $obj->id;

        $xml .= Ext_Xml::cdata(
            'title',
            '<a href="' . $obj->getUri() .
            (empty($gCache) ? '' : '?no-cache') . '">' .
            $obj->getTitle() . '</a>'
        );

        $xml .= $form->getXml();

    } else {
        $attrs['is-new'] = 1;
        $xml .= Ext_Xml::cdata('title', 'Добавление');
        $xml .= $form->getXml();
    }

    $page->addContent(Ext_Xml::node('module', $xml, $attrs));
}

$page->output();
