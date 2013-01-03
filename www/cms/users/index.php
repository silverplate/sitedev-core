<?php

require_once '../prepend.php';
require_once 'filter-lib.php';

$page = new App_Cms_Back_Page();
$page->SetTitle($g_section->GetTitle());

if ($page->IsAuthorized()) {
    if (isset($_GET['id'])) {
        $obj = App_Cms_User::Load($_GET['id']);
        if (!$obj) unset($obj);

    } elseif (isset($_GET['NEW'])) {
        $obj = new App_Cms_User();
    }

    if (isset($obj)) {
        $form = new App_Form();
        $form->Load('form.xml');

        if ($obj->GetId()) {
            $form->FillFields($obj->toArray());
            $form->Elements['passwd']->SetValue();

            if ($obj->statusId != 1) {
                $form->Elements['status_id']->SetValue(0);
            }

            $form->CreateButton('Сохранить', 'update');
            $form->CreateButton('Удалить', 'delete');

        } else {
            $form->CreateButton('Сохранить', 'insert');
            $form->Elements['passwd']->SetRequired(true);
        }

        $form->Execute();

        if ($form->UpdateStatus == FORM_UPDATED) {
            if (isset($form->Buttons['delete']) && $form->Buttons['delete']->IsSubmited()) {
                $obj->Delete();
                App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_DELETE, $obj->GetId(), $obj->getTitle());
                goToUrl($page->getUrl('path') . '?DEL');

            } else if ((isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) || (isset($form->Buttons['update']) && $form->Buttons['update']->IsSubmited())) {
                if (App_Cms_User::CheckUnique($form->Elements['email']->GetValue(), $obj->GetId())) {
                    $obj->fillWithData($form->GetSqlValues());

                    $password = $form->Elements['passwd']->GetValue();
                    if (isset($password['password'])) {
                        $obj->SetPassword($password['password']);
                    }

                    if ($form->Elements['status_id']->GetValue() != 1) {
                        $obj->statusId = 2;
                    }

                    if (isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) {
                        $obj->Create();
                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_CREATE, $obj->GetId(), $obj->getTitle());
                    } else {
                        $obj->Update();
                        App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $obj->GetId(), $obj->getTitle());
                    }

                    goToUrl($page->getUrl('path') . '?id=' . $obj->GetId() . '&OK');

                } else {
                    $form->UpdateStatus = FORM_ERROR;
                    $form->Elements['email']->SetUpdateType(FIELD_ERROR_EXIST);
                    $form->Elements['email']->SetErrorValue($form->Elements['email']->GetValue());
                    $form->Elements['email']->SetValue($obj->email);
                }
            }
        }

        if ($form->UpdateStatus == FORM_ERROR) {
            $page->SetUpdateStatus('error');

        } elseif (isset($_GET['OK'])) {
            $page->SetUpdateStatus('success');
        }

    } elseif (isset($_GET['DEL'])) {
        $page->SetUpdateStatus('success', 'Пользователь удален');
    }

    $filter = obj_get_filter();
    $listXml = '<local-navigation type="filter"';

    $options = array('open', 'name', 'email');
    foreach ($options as $item) {
        if (isset($filter['is_' . $item]) && $filter['is_' . $item]) $listXml .= ' is-' . $item . '="true"';
    }

    $listXml .= '>';

    foreach (array('name', 'email') as $item) {
        if (isset($filter[$item]) && $filter[$item]) {
            $listXml .= '<filter-' . $item . '><![CDATA[' . $filter[$item] . ']]></filter-' . $item . '>';
        }
    }

    $listXml .= '</local-navigation>';

    if (isset($obj)) {
        $module = '<module type="simple" is-able-to-add="true"';

        if ($obj->GetId()) {
            $module .= ' id="' . $obj->GetId() . '">';
            $module .= '<title><![CDATA[' . $obj->getTitle() . ']]></title>';
        } else {
            $module .= ' is-new="true">';
            $module .= '><title><![CDATA[Добавление]]></title>';
        }

        $module .= $form->GetXml();
        $module .= $listXml;
        $module .= '</module>';

        $page->AddContent($module);

    } else {
        $about = $g_section->description ? '<p class="first">' . $g_section->description . '</p>' : '';
        $page->AddContent('<module type="simple" is_able_to_add="true">' . $listXml . '<content><html><![CDATA[' . $about . ']]></html></content></module>');
    }
}

$page->Output();
