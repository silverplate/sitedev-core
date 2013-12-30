<?php

require_once '../prepend.php';

global $gIsUsers;

$page = new \App\Cms\Back\Page();

if ($page->isAllowed()) {
    $id = empty($_GET['id']) ? false : $_GET['id'];
    $document = empty($_GET['parent_id'])
              ? false
              : \App\Cms\Front\Document::getById($_GET['parent_id']);

    if (!$document) {
        documentNotFound();
    }
}

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = \App\Cms\Front\Data::getById($_GET['id']);
        if (!$obj) reload('?parent_id=' . $document->id);

    } else {
        $obj = new \App\Cms\Front\Data();
        $obj->frontDocumentId = $document->id;
    }

    $page->setTitle($obj->id ? $obj->getTitle() : 'Добавление');


    // Форма редактирования или добавления объекта

    $form = \App\Cms\Ext\Form::load(dirname(__FILE__) . '/data-form.xml');

    // Тип данных
    foreach (
        \App\Cms\Front\Data\ContentType::getList(array('is_published' => 1)) as
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
        \App\Cms\Front\Controller::getPri(),
        $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
    ));

    $form->frontControllerId->addOption(null, 'Нет');

    foreach (\App\Cms\Front\Controller::getList(array('type_id' => 2)) as $item) {
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

        foreach (\App\Cms\User::getAuthGroups() as $id => $params) {
            $form->authStatusId->addOption(
                $id,
                \Ext\String::toLower($params['title1'])
            );
        }
    }

    // Копирование блока в дочерние документы
    foreach (\App\Cms\Front\Data::getApplyTypes() as $id => $title) {
        $form->applyTypeId->addOption($id, \Ext\String::toLower($title));
    }

    $form->fillWithObject($obj);
    $form->run();

    if ($form->isSubmited() && $form->isSuccess()) {
        if ($form->isSubmited('delete')) {
            $obj->delete();

            \App\Cms\Back\Log::logModule(
                \App\Cms\Back\Log::ACT_DELETE,
                $obj->id,
                $obj->getTitle()
            );

            \App\Cms\Ext\Form::saveCookieStatus();
            reload('?parent_id=' . $obj->frontDocumentId);

        } else {
            $obj->fillWithData($form->toArray());

            if (!$obj->authStatusId) {
                $obj->authStatusId = \App\Cms\User::AUTH_GROUP_ALL;
            }

            $obj->save();

            \App\Cms\Back\Log::logModule(
                $form->isSubmited('insert') ? \App\Cms\Back\Log::ACT_CREATE : \App\Cms\Back\Log::ACT_MODIFY,
                $obj->id,
                $obj->getTitle()
            );

            \App\Cms\Ext\Form::saveCookieStatus();
            reload('?id=' . $obj->id . '&parent_id=' . $obj->frontDocumentId);
        }
    }

    if (!$form->isSubmited() && \App\Cms\Ext\Form::wasCookieStatus()) {
        $page->addContent(\Ext\Xml::cdata(
            'update-parent',
            'documentUpdateDataBlocks();'
        ));
    }

    $page->addContent($form->getXml());
    $page->setTemplate(TEMPLATES . 'back/popup.xsl');
}

$page->output();
