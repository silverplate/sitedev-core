<?php

require_once '../prepend.php';

$page = new \App\Cms\Back\Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = \App\Cms\Back\Section::getById($_GET['id']);
        if (!$obj) reload();

    } else if (key_exists('add', $_GET)) {
        $obj = new \App\Cms\Back\Section();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = \App\Cms\Ext\Form::load(dirname(__FILE__) . '/form.xml');
        $form->fillWithObject($obj);

        foreach (\App\Cms\Back\User::getList() as $item) {
            $form->users->addOption($item->id, $item->getTitle());
        }

        if ($obj->id) {
            $form->users->setValue($obj->getLinkIds('users'));
        }

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
                redirect($page->getUrl('path'));

            } else {
                $obj->fillWithData($form->toArray());

                if ($obj->checkUnique()) {
                    $obj->save();

                    \App\Cms\Back\Log::logModule(
                        $form->isSubmited('insert') ? \App\Cms\Back\Log::ACT_CREATE : \App\Cms\Back\Log::ACT_MODIFY,
                        $obj->id,
                        $obj->getTitle()
                    );

                    $obj->updateLinks('users', $form->users->getValue());

                    \App\Cms\Ext\Form::saveCookieStatus();
                    reload('?id=' . $obj->id);

                } else {
                    $form->setUpdateStatus(\App\Cms\Ext\Form::ERROR);
                    $form->uri->setUpdateStatus(\Ext\Form\Element::ERROR_EXIST);
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


    // Внутренняя навигация

    $filterXml = '';

    foreach (\App\Cms\Back\Section::getList() as $item) {
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
        if (\App\Cms\Back\Section::get()->description) {
            $xml .= \Ext\Xml::notEmptyNode('content', \Ext\Xml::cdata(
                'html',
                '<p class="first">' . \App\Cms\Back\Section::get()->description . '</p>'
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
