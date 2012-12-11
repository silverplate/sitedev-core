<?php

require('../prepend.php');

$page = new Page();
$page->SetRootNodeName('http_request');
$page->SetRootNodeAttribute('type', 'document_data');
$page->SetTemplate(TEMPLATES . 'bo_http_requests.xsl');

$data = $_POST;

if (isset($data['id']) && $data['id']) {
	$page->SetRootNodeAttribute('parent_id', $data['id']);
	$page->AddContent(get_branch_xml($data['id']));
}

header('Content-type: text/html; charset=utf-8');
$page->Output();


function get_branch_xml($_parent_id) {
	$result = '';
	$document = Document::Load($_parent_id);

	foreach (DocumentData::GetList(array(Document::GetPri() => $_parent_id)) as $item) {
		$additional_xml = '';

		switch ($item->GetTypeId()) {
			case 'image':
				if ($document && is_dir($document->GetFilePath())) {
					if ($document->GetImages()) {
						$additional_xml .= '<self>';
						foreach ($document->GetImages() as $image) {
							$additional_xml .= $image->GetXml();
						}
						$additional_xml .= '</self>';
					}
				}

				if (!isset($other_images)) {
					$other_images = data_get_images(DOCUMENT_ROOT . 'f/', $document->GetFilePath());
				}

				if ($other_images) {
					$additional_xml .= '<others>';
					foreach ($other_images as $image) {
						$additional_xml .= $image->GetXml();
					}
					$additional_xml .= '</others>';
				}

				break;
		}

		$result .= $item->GetXml($additional_xml);
	}

	return $result;
}

function data_get_images($_dir, $_exclude_path) {
	$result = array();
	$dir = rtrim($_dir, '/') . '/';
	$exclude_path = rtrim($_exclude_path, '/') . '/';

	if (is_dir($dir)) {
		$dir_handle = opendir($dir);
		$item = readdir($dir_handle);

		while ($item) {
			if ($item != '.' && $item != '..') {
				if (is_dir($dir . $item)) {
					$result = array_merge($result, data_get_images($dir . $item, $exclude_path));

				} else if ($dir != $exclude_path && Ext_Image::IsImage($item)) {
				    $result[] = App_Image::factory($dir . $item);
				}
			}

			$item = readdir($dir_handle);
		}

		closedir($dir_handle);
	}

	return $result;
}
