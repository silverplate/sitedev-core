<?php

require_once '../prepend.php';

global $gCache, $gIsUsers;

$page = new \App\Cms\Back\Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = \App\Cms\Front\Document::getById($_GET['id']);
        if (!$obj) reload();

    } else if (array_key_exists('add', $_GET)) {
        $obj = new \App\Cms\Front\Document();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = \App\Cms\Ext\Form::load(dirname(__FILE__) . '/document-form.xml');

        // Добавление документа в навигацию
        foreach (
            \App\Cms\Front\Navigation::getList(array('is_published' => 1)) as
            $item
        ) {
            $form->navigations->addOption($item->getId(), $item->getTitle());
        }

        // Контроллер
        $used = \Ext\Db::get()->getList(\Ext\Db::get()->getSelect(
            $obj->getTable(),
            \App\Cms\Front\Controller::getPri(),
            $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
        ));

        foreach (\App\Cms\Front\Controller::getList(array('type_id' => 1)) as $item) {
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
        $used = \Ext\Db::get()->getList(\Ext\Db::get()->getSelect(
            $obj->getTable(),
            \App\Cms\Front\Template::getPri(),
            $obj->id ? array($obj->getPrimaryKeyWhereNot()) : null
        ));

        foreach (
            \App\Cms\Front\Template::getList(null, array('order' => 'is_document_main DESC, title')) as
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

            foreach (\App\Cms\User::getAuthGroups() as $id => $params) {
                $form->authStatusId->addOption(
                    $id,
                    \Ext\String::toLower($params['title1'])
                );
            }
        }

        // Данные
        $form->fillWithObject($obj);

        if ($obj->id) {
            $form->getGroup('content')->addAdditionalXml(\Ext\Xml::node('document-data'));
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

                \App\Cms\Back\Log::logModule(
                    \App\Cms\Back\Log::ACT_DELETE,
                    $obj->id,
                    $obj->getTitle()
                );

                \App\Cms\Ext\Form::saveCookieStatus();

                redirect($page->getUrl('path'));

            } else {
                $obj->fillWithData($form->toArray());

                if (!$obj->checkFolder() || !$obj->checkRoot()) {
                    $form->setUpdateStatus(\App\Cms\Ext\Form::ERROR);
                    $form->folder->setUpdateStatus(\Ext\Form\Element::ERROR_SPELLING);

                } else if (!$obj->checkUnique()) {
                    $form->setUpdateStatus(\App\Cms\Ext\Form::ERROR);
                    $form->folder->setUpdateStatus(\Ext\Form\Element::ERROR_EXIST);

                } else {
                    $obj->save();

                    \App\Cms\Back\Log::logModule(
                        $form->isSubmited('insert') ? \App\Cms\Back\Log::ACT_CREATE : \App\Cms\Back\Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    if ($form->isSubmited('update')) {
                        foreach (\App\Cms\Front\Data::getList(array(
                            $obj->getPrimaryKeyWhere(),
                            'is_mount' => 1
                        )) as $data) {
                            $key = 'document_data_form_ele_' . $data->id;

                            if (array_key_exists($key, $_POST)) {
                                $data->updateAttr(
                                    'content',
                                    $data->getParsedContent($_POST[$key])
                                );

                                \App\Cms\Back\Log::LogModule(
                                    \App\Cms\Back\Log::ACT_MODIFY,
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

                    \App\Cms\Ext\Form::saveCookieStatus();

                    reload('?id=' . $obj->id);
                }
            }
        }
    }


    // Статус обработки формы

    $formStatusXml = '';

    if (!isset($form) || !$form->isSubmited()) {
        $formStatusXml = \App\Cms\Ext\Form::getCookieStatusXml(
            empty($obj) ? 'Выполнено' : 'Данные сохранены'
        );

        \App\Cms\Ext\Form::clearCookieStatus();
    }


    // XML модуля

    $xml = $formStatusXml;
    $attrs = array(
        'type' => 'tree',
        'is-able-to-add' => 'true',
        'name' => \App\Cms\Back\Section::get()->getName()
    );

    if (empty($obj)) {
        if (\App\Cms\Back\Section::get()->description) {
            $xml .= \Ext\Xml::notEmptyNode('content', \Ext\Xml::cdata(
                'html',
                '<p class="first">' . \App\Cms\Back\Section::get()->description . '</p>'
            ));
        }

    } else if ($obj->getId()) {
        $attrs['id'] = $obj->id;

        $xml .= \Ext\Xml::cdata(
            'title',
            '<a href="' . $obj->getUri() .
            (empty($gCache) ? '' : '?no-cache') . '">' .
            $obj->getTitle() . '</a>'
        );

        $xml .= $form->getXml();

    } else {
        $attrs['is-new'] = 1;
        $xml .= \Ext\Xml::cdata('title', 'Добавление');
        $xml .= $form->getXml();
    }

    $page->addContent(\Ext\Xml::node('module', $xml, $attrs));
}

$page->output();
