<?php

require('../prepend.php');

$page = new App_Cms_Back_Page();
$page->SetTitle($g_section->GetTitle());

if ($page->IsAuthorized()) {
	if (isset($_GET['id'])) {
		$obj = App_Cms_Back_User::Load($_GET['id']);
		if (!$obj) unset($obj);

	} elseif (isset($_GET['NEW'])) {
		$obj = new App_Cms_Back_User;
	}

	if (isset($obj)) {
		$form = new App_Form();
		$form->Load('form.xml');

		foreach (App_Cms_Back_Section::GetList() as $item) {
			$form->Elements['sections']->AddOption($item->GetId(), $item->GetTitle());
		}

		if ($obj->getId()) {
			$form->FillFields($obj->toArray());
			$form->Elements['passwd']->SetValue();
			$form->Elements['sections']->SetValue($obj->GetLinkIds('sections'));

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
				App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_DELETE, $obj->getId(), $obj->getTitle());
				goToUrl($page->getUrl('path') . '?DEL');

			} elseif ((isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) || (isset($form->Buttons['update']) && $form->Buttons['update']->IsSubmited())) {
				if (App_Cms_Back_User::CheckUnique($form->Elements['login']->GetValue(), $obj->getId())) {
					$obj->fillWithData($form->GetSqlValues());

					$password = $form->Elements['passwd']->GetValue();
					if (isset($password['password'])) {
						$obj->SetPassword($password['password']);
					}

					if ($form->Elements['status_id']->GetValue() != 1) {
						$obj->statusId = 2;
					}

					if ($obj->ipRestriction) {
						$obj->ipRestriction = implode("\r\n", Ext_String::split($obj->ipRestriction));
					}

					if (isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) {
						$obj->Create();
						App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_CREATE, $obj->getId(), $obj->getTitle());
					} else {
						$obj->Update();
						App_Cms_Back_Log::LogModule(App_Cms_Back_Log::ACT_MODIFY, $obj->getId(), $obj->getTitle());
					}

					if (isset($form->Elements['sections'])) {
						$obj->UpdateLinks('sections', $form->Elements['sections']->GetValue());
					}

					if ($form->Elements['passwd']->GetValue() && App_Cms_Session::Get()->GetUserId() != $obj->getId()) {
						App_Cms_Session::Clean($obj->getId());
					}

					goToUrl($page->getUrl('path') . '?id=' . $obj->getId() . '&OK');

				} else {
					$form->UpdateStatus = FORM_ERROR;

					$form->Elements['login']->SetUpdateType(FIELD_ERROR_EXIST);
					$form->Elements['login']->SetErrorValue($form->Elements['login']->GetValue());
					$form->Elements['login']->SetValue($obj->login);
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

	$listXml = '<local-navigation>';
	foreach (App_Cms_Back_User::GetList() as $item) {
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
			$module .= '><title><![CDATA[Добавление]]></title>';
		}

		$module .= $form->GetXml();
		$module .= $listXml;
		$module .= '</module>';

		$page->AddContent($module);

	} else {
		$about = $g_section->description ? '<p class="first">' . $g_section->description . '</p>' : '';
		$page->AddContent('<module type="simple" is-able-to-add="true">' . $listXml . '<content><html><![CDATA[' . $about . ']]></html></content></module>');
	}
}

$page->Output();

?>
