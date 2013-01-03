<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();
$page->SetTitle($g_section->GetTitle());

if ($page->IsAuthorized()) {
    if (isset($_GET['id'])) {
        $obj = App_Cms_Front_Document::Load($_GET['id']);
        if (!$obj) unset($obj);

    } elseif (isset($_GET['NEW'])) {
        $obj = new App_Cms_Front_Document();
    }

    if (isset($obj)) {
        $templateKey = App_Cms_Front_Template::getPri();
        $documentTbl = App_Cms_Front_Document::getTbl();
        $documentKey = App_Cms_Front_Document::getPri();

        $form = new App_Form();
        $form->Load('document-form.xml');

        foreach (App_Cms_Front_Navigation::GetList(array('is_published' => 1)) as $item) {
            $form->Elements['navigations']->AddOption($item->GetId(), $item->GetTitle());
        }

        $tmp = array();
        foreach ($form->Elements as $name => $ele) {
            if ($name == 'front_controller_id') {
                $key = App_Cms_Front_Controller::getPri();
                $tmp[$key] = $ele;
                $tmp[$key]->SetName($key);

            } else if ($name == 'front_template_id') {
                $tmp[$templateKey] = $ele;
                $tmp[$templateKey]->setName($templateKey);

            } else {
                $tmp[$name] = $ele;
            }
        }
        $form->Elements = $tmp;
        unset($tmp);

        $controller_row_conditions = array();
        $controller_self_condition = isset($obj) && $obj && $obj->frontControllerId ? ' OR ' . App_Cms_Front_Controller::GetPri() . ' = ' . App_Db::escape($obj->frontControllerId) : '';
        $used = App_Db::Get()->GetList('SELECT ' . App_Cms_Front_Controller::GetPri() . ' FROM ' . App_Cms_Front_Document::GetTbl() . ' WHERE ' . App_Cms_Front_Controller::GetPri() . ' != ""' . (isset($obj) ? ' AND ' . App_Cms_Front_Document::getPri() . ' != ' . App_Db::escape($obj->getId()) : '') . ' GROUP BY ' . App_Cms_Front_Controller::GetPri());
        if ($used) array_push($controller_row_conditions, '(is_multiple = 1 OR ' . App_Cms_Front_Controller::GetPri() . ' NOT IN (' . App_Db::escape($used) . ')' . $controller_self_condition . ')');
        array_push($controller_row_conditions, $controller_self_condition ? '(is_published = 1' . $controller_self_condition . ')' : 'is_published = 1');

        foreach (App_Cms_Front_Controller::GetList(array('type_id' => 1), null, $controller_row_conditions) as $item) {
            $form->Elements[App_Cms_Front_Controller::GetPri()]->AddOption($item->GetId(), $item->GetTitle());
        }

        $usedCond = '';
        if ($obj->getId()) {
            $usedCond = "WHERE $documentKey != " . $obj->getSqlId();
        }

        $mainTemplateId = null;
        $used = App_Db::get()->getList("SELECT $templateKey
                                    FROM $documentTbl
                                    $usedCond
                                    GROUP BY $templateKey");
        $templates = array();

        foreach (
            App_Cms_Front_Template::getList(null, array('order' => 'is_document_main DESC, title')) as
            $id => $item
        ) {
            if (
                ($obj->getId() && $obj->$templateKey == $id) ||
                ($item->isPublished && ($item->isMultiple || !in_array($id, $used)))
            ) {
                $templates[$id] = $item->getTitle();
            }

            if ($item->isDocumentMain && $item->isPublished) {
                $mainTemplateId = $id;
            }
        }

        foreach ($templates as $id => $title) {
            $form->Elements[$templateKey]->addOption($id, $title);
        }

        if (IS_USERS) {
            $form->Groups['system']->AddElement($form->CreateElement('auth_status_id', 'chooser', 'Страница доступна'));
            foreach (App_Cms_User::GetAuthGroups() as $id => $params) {
                $form->Elements['auth_status_id']->AddOption($id, Ext_String::toLower($params['title1']));
            }
        }

        if ($obj->getId()) {
            $form->FillFields($obj->toArray());

            $form->Elements['navigations']->SetValue($obj->GetLinkIds('navigations'));

            foreach ($obj->GetFiles() as $item) {
                $form->Elements['files']->AddAdditionalXml($item->GetXml());
            }

            $form->Groups['content']->addAdditionalXml('<document-data />');
            $form->CreateButton('Сохранить', 'update');
            $form->CreateButton('Удалить', 'delete');

        } else {
            if ($mainTemplateId) {
                $form->Elements[$templateKey]->setValue($mainTemplateId);
            }

            unset($form->Groups['content']);
            $form->CreateButton('Сохранить', 'insert');
        }

        $form->Execute();

        if ($form->UpdateStatus == FORM_UPDATED) {
            $is_root = (!isset($form->Elements['folder']) || $form->Elements['folder']->GetValue() != '/' || $form->Elements['parent_id']->GetValue() == '');
            $is_unique = (!isset($form->Elements['parent_id']) || App_Cms_Front_Document::CheckUnique($form->Elements['parent_id']->GetValue(), $form->Elements['folder']->GetValue(), $obj->getId()));

            if ($is_root && $is_unique) {
                $obj->fillWithData($form->GetSqlValues());

//                 if (!$obj->parent_id) {
//                     $obj->parentId = 'NULL';
//                 }

                if (isset($form->Buttons['delete']) && $form->Buttons['delete']->IsSubmited()) {
                    $obj->Delete();
                    App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_DELETE, $obj->getId(), $obj->getTitle());
                    reload('?DEL');

                } elseif ((isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) || (isset($form->Buttons['update']) && $form->Buttons['update']->IsSubmited())) {
                    if (isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) {
                        $obj->Create();
                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_CREATE, $obj->getId(), $obj->getTitle());
                    } else {
                        $obj->Update();
                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $obj->getId(), $obj->getTitle());

                        foreach (App_Cms_Front_Data::GetList(array(App_Cms_Front_Document::getPri() => $obj->getId(), 'is_mount' => 1)) as $data) {
                            if (isset($_POST['document_data_form_ele_' . $data->GetId()])) {
                                $data->updateAttr('content', $data->GetParsedContent($_POST['document_data_form_ele_' . $data->GetId()]));
                                App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $data->GetId(), 'Блоки данных. Документ ' . $obj->getId());
                            }
                        }
                    }

                    $filesHaveBeenChanged = false;
                    $files = &$form->Elements['files']->GetValue();

                    if ($files && is_array($files) && isset($files[0])) {
                        foreach ($files as $file) {
                            if (isset($file['name']) && isset($file['tmp_name'])) {
                                $filesHaveBeenChanged = true;
                                $obj->UploadFile($file['name'], $file['tmp_name']);
                            }
                        }
                    }

                    if ($filesHaveBeenChanged) {
                        $obj->cleanFileCache();
                    }

                    $obj->UpdateLinks('navigations', $form->Elements['navigations']->GetValue());
                    reload('?id=' . $obj->getId() . '&OK');
                }

            } else {
                $form->UpdateStatus = FORM_ERROR;
                $form->Elements['folder']->SetUpdateType((!$is_root) ? FIELD_ERROR_SPELLING : FIELD_ERROR_EXIST);
                $form->Elements['folder']->SetErrorValue($form->Elements['folder']->GetValue());
                $form->Elements['folder']->SetValue($obj->folder);
            }
        }

        if ($form->UpdateStatus == FORM_ERROR) {
            $page->SetUpdateStatus('error');

        } elseif (isset($_GET['OK'])) {
            $page->SetUpdateStatus('success');
        }

    } elseif (isset($_GET['DEL'])) {
        $page->SetUpdateStatus('success', 'Страница удалена');
    }

    if (isset($obj) && $obj) {
        $module = '<module type="tree" name="' . $g_section->GetName() . '" is_able_to_add="true"';

        if ($obj->getId()) {
            $module .= ' id="' . $obj->getId() . '" file_path="' . $obj->GetFilePath() . '">';
            $module .= '<title><![CDATA[<a href="' . $obj->GetUrl() . '?' . ($obj->isPublished ? 'no_cache' : 'key=' . SITE_KEY) . '" target="_blank" title="Посмотреть на сайте">' . $obj->getTitle() . '</a>]]></title>';
        } else {
            $module .= ' is_new="true">';
            $module .= '><title><![CDATA[Добавление]]></title>';
        }

        $module .= $form->GetXml();
        $module .= '</module>';

        $page->AddContent($module);

    } else {
        $about = $g_section->description ? '<p class="first">' . $g_section->description . '</p>' : '';
        $page->AddContent('<module type="tree" name="' . $g_section->GetName() . '" is_able_to_add="true"><content><html><![CDATA[' . $about . ']]></html></content></module>');
    }
}

$page->Output();
