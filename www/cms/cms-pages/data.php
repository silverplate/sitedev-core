<?php

require_once '../prepend.php';

$page = new App_Cms_Back_Page();
$page->SetTemplate(TEMPLATES . 'back/popup.xsl');

$document_id = isset($_GET['parent_id']) && $_GET['parent_id'] ? $_GET['parent_id'] : null;
$id = isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : null;

if (is_null($document_id) || !App_Cms_Front_Document::Load($document_id)) {
    $page->AddContent('<html>Неизвестный документ.</html>');

} else {
    if ($id) {
        $obj = App_Cms_Front_Data::Load($id);
        if (!$obj) unset($obj);
    }

    $form = new App_Form();
    $form->Load('data-form.xml');

    $tmp = array();
    foreach ($form->Elements as $name => $ele) {
        if ($name == 'front_data_content_type_id') {
            $tmp[App_Cms_Front_Data_ContentType::GetPri()] = $ele;
            $tmp[App_Cms_Front_Data_ContentType::GetPri()]->SetName(App_Cms_Front_Data_ContentType::GetPri());

        } else if ($name == 'front_controller_id') {
            $tmp[App_Cms_Front_Controller::GetPri()] = $ele;
            $tmp[App_Cms_Front_Controller::GetPri()]->SetName(App_Cms_Front_Controller::GetPri());

        } else {
            $tmp[$name] = $ele;
        }
    }
    $form->Elements = $tmp;
    unset($tmp);

    foreach (App_Cms_Front_Data_ContentType::GetList(array('is_published' => 1)) as $item) {
        $form->Elements[App_Cms_Front_Data_ContentType::GetPri()]->AddOption($item->GetId(), $item->GetTitle());
    }

    $controller_row_conditions = array();
    $controller_self_condition = isset($obj) && $obj && $obj->frontControllerId ? ' OR ' . App_Cms_Front_Controller::getPri() . ' = ' . App_Db::escape($obj->frontControllerId) : '';
    $used = App_Db::Get()->GetList('SELECT ' . App_Cms_Front_Controller::GetPri() . ' FROM ' . App_Cms_Front_Data::GetTbl() . ' WHERE ' . App_Cms_Front_Controller::GetPri() . ' != ""' . (isset($obj) ? ' AND ' . App_Cms_Front_Data::GetPri() . ' != ' . App_Db::escape($obj->GetId()) : '') . ' GROUP BY ' . App_Cms_Front_Controller::GetPri());
    if ($used) array_push($controller_row_conditions, '(is_multiple = 1 OR ' . App_Cms_Front_Controller::GetPri() . ' NOT IN (' . App_Db::escape($used) . ')' . $controller_self_condition . ')');
    array_push($controller_row_conditions, $controller_self_condition ? '(is_published = 1' . $controller_self_condition . ')' : 'is_published = 1');

    $form->Elements[App_Cms_Front_Controller::GetPri()]->AddOption('', 'Нет');
    foreach (App_Cms_Front_Controller::GetList(array('type_id' => 2), null, $controller_row_conditions) as $item) {
        $form->Elements[App_Cms_Front_Controller::GetPri()]->AddOption($item->GetId(), $item->GetTitle());
    }

    if (IS_USERS) {
        $form->CreateElement('auth_status_id', 'chooser', 'Данные доступны');
        foreach (App_Cms_User::GetAuthGroups() as $id => $params) {
            $form->Elements['auth_status_id']->AddOption($id, Ext_String::toLower($params['title1']));
        }
    }

    foreach (App_Cms_Front_Data::GetApplyTypes() as $id => $title) {
        $form->Elements['apply_type_id']->AddOption($id, Ext_String::toLower($title));
    }

    if (!isset($obj) || !$obj) {
        $page->SetTitle('Добавление');
        $obj = new App_Cms_Front_Data();
        $form->CreateButton('Сохранить', 'create');

    } else {
        $page->SetTitle($obj->getTitle());
        $form->FillFields($obj->toArray());
        $form->CreateButton('Сохранить', 'update');
        $form->CreateButton('Удалить', 'delete');
    }

    $obj->frontDocumentId = $document_id;
    $form->Execute();

    if ($form->UpdateStatus == FORM_UPDATED) {
        $obj->fillWithData($form->GetSqlValues());

        if (isset($form->Buttons['delete']) && $form->Buttons['delete']->IsSubmited()) {
            $obj->Delete();
            App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_DELETE, $obj->getId(), 'Блоки данных. Документ ' . $obj->frontDocumentId);
            goToUrl($page->getUrl('path') . '?parent_id=' . $obj->frontDocumentId . '&DEL');

        } else if (isset($form->Buttons['create']) && $form->Buttons['create']->IsSubmited()) {
            $obj->Create();
            App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_CREATE, $obj->getId(), 'Блоки данных. Документ ' . $obj->frontDocumentId);
            goToUrl($page->getUrl('path') . '?id=' . $obj->getId() . '&parent_id=' . $obj->frontDocumentId . '&OK');

        } else if (isset($form->Buttons['update']) && $form->Buttons['update']->IsSubmited()) {
            $obj->Update();
            App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $obj->getId(), 'Блоки данных. Документ ' . $obj->frontDocumentId);
            goToUrl($page->getUrl('path') . '?id=' . $obj->getId() . '&parent_id=' . $obj->frontDocumentId . '&OK');
        }
    }

    if ($form->UpdateStatus == FORM_ERROR) {
        $page->setUpdateStatus('error');

    } else if (isset($_GET['OK'])) {
        $page->setUpdateStatus('success');
        $page->addContent('<update-parent>documentUpdateDataBlocks()</update-parent>');

    } else if (isset($_GET['DEL'])) {
        $page->setUpdateStatus('success', 'Данные удалены.');
        $page->addContent('<update-parent>documentUpdateDataBlocks()</update-parent>');
    }

    $page->addContent($form->getXml());
}

$page->output();
