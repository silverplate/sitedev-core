<?php

require_once '../prepend.php';

global $gIsUsers;

$page = new App_Cms_Back_Page();

if ($page->isAllowed()) {
    $id = empty($_GET['id']) ? false : $_GET['id'];
    $document = empty($_GET['parent_id'])
              ? false
              : App_Cms_Front_Document::getById($_GET['parent_id']);

    if (!$document) {
        documentNotFound();
    }
}

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = App_Cms_Front_Data::getById($_GET['id']);
        if (!$obj) reload('?parent_id=' . $document->id);

    } else {
        $obj = new App_Cms_Front_Data();
        $obj->frontDocumentId = $document->id;
    }

    $page->setTitle($obj->id ? $obj->getTitle() : 'Добавление');


    // Форма редактирования или добавления объекта

    $form = App_Cms_Ext_Form::load(dirname(__FILE__) . '/data-form.xml');

    // Тип данных
    foreach (
        App_Cms_Front_Data_ContentType::getList(array('is_published' => 1)) as
        $item
    ) {
        $form->frontDataContentTypeId->addOption(
            $item->id,
            $item->getTitle()
        );
    }

    // Контроллер
    $used = \Ext\Db::get()->getList(\Ext\Db::get()->getSelect(
        $obj->getTable(),
        App_Cms_Front_Controller::getPri(),
        $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
    ));

    $form->frontControllerId->addOption(null, 'Нет');

    foreach (App_Cms_Front_Controller::getList(array('type_id' => 2)) as $item) {
        if (
            $item->id == $obj->frontControllerId || (
                $item->isPublished &&
                $item->isMultiple || !in_array($item->id, $used)
            )
        ) {
            $form->frontControllerId->addOption($item->id, $item->getTitle());
        }
    }

    // Доступ для групп пользователей
    if (!empty($gIsUsers)) {
        $form->createElement('auth_status_id', 'chooser', 'Данные доступны');

        foreach (App_Cms_User::getAuthGroups() as $id => $params) {
            $form->authStatusId->addOption(
                $id,
                \Ext\String::toLower($params['title1'])
            );
        }
    }

    // Копирование блока в дочерние документы
    foreach (App_Cms_Front_Data::getApplyTypes() as $id => $title) {
        $form->applyTypeId->addOption($id, \Ext\String::toLower($title));
    }

    $form->fillWithObject($obj);
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
            reload('?parent_id=' . $obj->frontDocumentId);

        } else {
            $obj->fillWithData($form->toArray());

            if (!$obj->authStatusId) {
                $obj->authStatusId = App_Cms_User::AUTH_GROUP_ALL;
            }

            $obj->save();

            App_Cms_Back_Log::logModule(
                $form->isSubmited('insert') ? App_Cms_Back_Log::ACT_CREATE : App_Cms_Back_Log::ACT_MODIFY,
                $obj->id,
                $obj->getTitle()
            );

            App_Cms_Ext_Form::saveCookieStatus();
            reload('?id=' . $obj->id . '&parent_id=' . $obj->frontDocumentId);
        }
    }

    if (!$form->isSubmited() && App_Cms_Ext_Form::wasCookieStatus()) {
        $page->addContent(\Ext\Xml::cdata(
            'update-parent',
            'documentUpdateDataBlocks();'
        ));
    }

    $page->addContent($form->getXml());
    $page->setTemplate(TEMPLATES . 'back/popup.xsl');
}

$page->output();
