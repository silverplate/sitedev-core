<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();
$page->setTitle($g_section->getTitle());

if ($page->isAuthorized()) {
    if (isset($_GET['id'])) {
        $obj = App_Cms_Front_Template::getById($_GET['id']);
        if (!$obj) {
            unset($obj);
        }

    } else if (isset($_GET['NEW'])) {
        $obj = new App_Cms_Front_Template();
    }

    if (isset($obj)) {
        $form = new App_Form();
        $form->load('form.xml');

        if ($obj->getId()) {
            $form->fillFields($obj->toArray());
            $form->Elements['content']->setValue($obj->getContent());

            $form->createButton('Сохранить', 'update');
            $form->createButton('Удалить', 'delete');

        } else {
            $form->createButton('Сохранить', 'insert');
        }

        $form->execute();

        if ($form->UpdateStatus == FORM_UPDATED) {
            $obj->fillWithData($form->getSqlValues());

            if (
                isset($form->Buttons['delete']) &&
                $form->Buttons['delete']->isSubmited()
            ) {
                $obj->delete();
                App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_DELETE, $obj->getId(), $obj->getTitle());
                goToUrl($page->getUrl('path') . '?DEL');

            } else if (
                (isset($form->Buttons['insert']) && $form->Buttons['insert']->isSubmited()) ||
                (isset($form->Buttons['update']) && $form->Buttons['update']->isSubmited())
            ) {
                if (App_Cms_Front_Template::isUnique('filename', $obj->filename, $obj->getId())) {
                    if (
                        isset($form->Buttons['insert']) &&
                        $form->Buttons['insert']->isSubmited()
                    ) {
                        $obj->create();

                        $content = $form->Elements['content']->getValue();
                        if (!$obj->getFile() || $content != '') {
                            $obj->setContent($content);
                        }

                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_CREATE, $obj->GetId(), $obj->getTitle());

                    } else {
                        $obj->update();
                        $obj->setContent($form->Elements['content']->getValue());

                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $obj->getId(), $obj->getTitle());
                    }

                    if ($obj->isDocumentMain) {
                        App_Db::get()->execute(
                            'UPDATE ' . App_Cms_Front_Template::getTbl() . ' ' .
                            'SET is_document_main = 0 ' .
                            'WHERE is_document_main = 1 AND ' . App_Cms_Front_Template::getPri() . ' != ' . $obj->getSqlId()
                        );
                    }

                    reload('?id=' . $obj->getId() . '&OK');

                } else {
                    $form->UpdateStatus = FORM_ERROR;
                    $form->Elements['filename']->setUpdateType(FIELD_ERROR_EXIST);
                    $form->Elements['filename']->setErrorValue($form->Elements['filename']->getValue());
                    $form->Elements['filename']->setValue($obj->filename);
                }
            }

        } else if ($form->UpdateStatus == FORM_ERROR) {
            $page->setUpdateStatus('error');

        } else if (isset($_GET['OK'])) {
            $page->setUpdateStatus('success');

        }

    } else if (isset($_GET['DEL'])) {
        $page->setUpdateStatus('success', 'Шаблон удален');
    }

    $listXml = '<local-navigation>';

    foreach (App_Cms_Front_Template::getList() as $item) {
        $listXml .= $item->getBackOfficeXml();
    }

    $listXml .= '</local-navigation>';

    if (isset($obj)) {
        $module = '<module type="simple" is-able-to-add="true"';

        if ($obj->getId()) {
            $module .= ' id="' . $obj->getId() . '">';
            $module .= '<title><![CDATA[' . $obj->getTitle() . ']]></title>';

        } else {
            $module .= ' is-new="true">';
            $module .= '><title>Добавление</title>';
        }

        $module .= $form->getXml();
        $module .= $listXml;
        $module .= '</module>';

        $page->addContent($module);

    } else {
        $about = $g_section->description ? '<p class="first">' . $g_section->description . '</p>' : '';
        $page->addContent('<module type="simple" is-able-to-add="true">' . $listXml . '<content><html><![CDATA[' . $about . ']]></html></content></module>');
    }
}

$page->output();
