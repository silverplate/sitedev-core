<?php

require_once '../prepend.php';

$page = new App_Cms_Bo_Page();
$page->setTitle($g_section->getTitle());

if ($page->isAuthorized()) {
    if (isset($_GET['id'])) {
        $obj = App_Cms_Template::getById($_GET['id']);
        if (!$obj) {
            unset($obj);
        }

    } else if (isset($_GET['NEW'])) {
        $obj = new App_Cms_Template();
    }

    if (isset($obj)) {
        $form = new App_Form();
        $form->load('form.xml');

        if ($obj->getId()) {
            $form->fillFields($obj->getDb()->toArray());
            $form->Elements['content']->setValue($obj->getContent());

            $form->createButton('Сохранить', 'update');
            $form->createButton('Удалить', 'delete');

        } else {
            $form->createButton('Сохранить', 'insert');
        }

        $form->execute();

        if ($form->UpdateStatus == FORM_UPDATED) {
            $obj->getDb()->fillWithData($form->getSqlValues());

            if (
                isset($form->Buttons['delete']) &&
                $form->Buttons['delete']->isSubmited()
            ) {
                $obj->delete();
                App_Cms_Bo_Log::LogModule(App_Cms_Bo_Log::ACT_DELETE, $obj->getId(), $obj->getTitle());
                goToUrl($page->Url['path'] . '?DEL');

            } else if (
                (isset($form->Buttons['insert']) && $form->Buttons['insert']->isSubmited()) ||
                (isset($form->Buttons['update']) && $form->Buttons['update']->isSubmited())
            ) {
                if (App_Cms_TemplateDb::isUnique('filename',
                                         $obj->filename,
                                         $obj->getId())) {
                    if (
                        isset($form->Buttons['insert']) &&
                        $form->Buttons['insert']->isSubmited()
                    ) {
                        $obj->create();

                        $content = $form->Elements['content']->getValue();
                        if (!$obj->getFile() || $content != '') {
                            $obj->setContent($content);
                        }

                        App_Cms_Bo_Log::LogModule(App_Cms_Bo_Log::ACT_CREATE, $obj->GetId(), $obj->GetTitle());

                    } else {
                        $obj->update();
                        $obj->setContent($form->Elements['content']->getValue());

                        App_Cms_Bo_Log::LogModule(App_Cms_Bo_Log::ACT_MODIFY, $obj->getId(), $obj->getTitle());
                    }

                    if ($obj->isDocumentMain) {
                        App_Db::get()->execute(
                            'UPDATE ' . App_Cms_TemplateDb::getTbl() . ' ' .
                            'SET is_document_main = 0 ' .
                            'WHERE is_document_main = 1 AND ' . App_Cms_TemplateDb::getPri() . ' != ' . $obj->getDbId()
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

    $listXml = '<local_navigation>';

    foreach (App_Cms_Template::getList() as $item) {
        $listXml .= $item->getXml('bo-list', 'item');
    }

    $listXml .= '</local_navigation>';

    if (isset($obj)) {
        $module = '<module type="simple" is_able_to_add="true"';

        if ($obj->getId()) {
            $module .= ' id="' . $obj->getId() . '">';
            $module .= '<title><![CDATA[' . $obj->getTitle() . ']]></title>';

        } else {
            $module .= ' is_new="true">';
            $module .= '><title>Добавление</title>';
        }

        $module .= $form->getXml();
        $module .= $listXml;
        $module .= '</module>';

        $page->addContent($module);

    } else {
        $about = $g_section->description ? '<p class="first">' . $g_section->description . '</p>' : '';
        $page->addContent('<module type="simple" is_able_to_add="true">' . $listXml . '<content><html><![CDATA[' . $about . ']]></html></content></module>');
    }
}

$page->output();