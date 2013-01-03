<?php

require('../prepend.php');

$page = new App_Cms_Back_Page();
$page->SetTitle($g_section->GetTitle());

if ($page->IsAuthorized()) {
	if (isset($_GET['id'])) {
		$obj = App_Cms_Front_Navigation::Load($_GET['id']);
		if (!$obj) unset($obj);

	} elseif (isset($_GET['NEW'])) {
		$obj = new App_Cms_Front_Navigation();
	}

	if (isset($obj)) {
		$form = new App_Form();
		$form->Load('form.xml');

		foreach (App_Cms_Front_Navigation::GetTypes() as $id => $params) {
			$form->Elements['type']->AddOption($id, Ext_String::toLower($params['title']));
		}

		if ($obj->GetId()) {
			$form->FillFields($obj->toArray());
			$form->CreateButton('Сохранить', 'update');
			$form->CreateButton('Удалить', 'delete');
		} else {
			$form->CreateButton('Сохранить', 'insert');
		}

		$form->Execute();

		if ($form->UpdateStatus == FORM_UPDATED) {
			$obj->fillWithData($form->GetSqlValues());

			if (isset($form->Buttons['delete']) && $form->Buttons['delete']->IsSubmited()) {
				$obj->Delete();
				App_Cms_Back_Log::logModule(App_Cms_Back_Log::ACT_DELETE, $obj->GetId(), $obj->getTitle());
				goToUrl($page->getUrl('path') . '?DEL');

			} elseif ((isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) || (isset($form->Buttons['update']) && $form->Buttons['update']->IsSubmited())) {
				if (isset($form->Buttons['insert']) && $form->Buttons['insert']->IsSubmited()) {
					$obj->Create();
					App_Cms_Back_Log::logModule(App_Cms_Back_Log::ACT_CREATE, $obj->GetId(), $obj->getTitle());
				} else {
					$obj->Update();
					App_Cms_Back_Log::logModule(App_Cms_Back_Log::ACT_MODIFY, $obj->GetId(), $obj->getTitle());
				}

				goToUrl($page->getUrl('path') . '?id=' . $obj->GetId() . '&OK');
			}

		} elseif ($form->UpdateStatus == FORM_ERROR) {
			$page->SetUpdateStatus('error');

		} elseif (isset($_GET['OK'])) {
			$page->SetUpdateStatus('success');

		}

	} elseif (isset($_GET['DEL'])) {
		$page->SetUpdateStatus('success', 'Тип удален');
	}

	$listXml = '<local-navigation>';
	foreach (App_Cms_Front_Navigation::GetList() as $item) {
		$listXml .= $item->getBackOfficeXml();
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
		$page->AddContent('<module type="simple" is-able-to-add="true">' . $listXml . '<content><html><![CDATA[' . $about . ']]></html></content></module>');
	}
}

$page->Output();

?>
