<?php

require_once '../prepend.php';

$page = new \App\Cms\Back\Page();

if ($page->isAllowed()) {

    // Инициализация объекта

    $obj = null;

    if (!empty($_GET['id'])) {
        $obj = \App\Cms\Front\Navigation::getById($_GET['id']);
        if (!$obj) reload();

    } else if (array_key_exists('add', $_GET)) {
        $obj = new \App\Cms\Front\Navigation();
    }


    // Форма редактирования или добавления объекта

    if ($obj) {
        $form = \App\Cms\Ext\Form::load(dirname(__FILE__) . '/form.xml');
        $form->fillWithObject($obj);

        foreach (\App\Cms\Front\Navigation::getTypes() as $id => $params) {
            $form->type->addOption($id, \Ext\String::toLower($params['title']));
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
                $obj->save();

                \App\Cms\Back\Log::logModule(
                    $form->isSubmited('insert') ? \App\Cms\Back\Log::ACT_CREATE : \App\Cms\Back\Log::ACT_MODIFY,
                    $obj->id,
                    $obj->getTitle()
                );

                \App\Cms\Ext\Form::saveCookieStatus();
                reload('?id=' . $obj->id);
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

    foreach (\App\Cms\Front\Navigation::getList() as $item) {
        $filterXml .= $item->getBackOfficeXml();
    }

    $filterXml = \Ext\Xml::node('local-navigation', $filterXml);


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
