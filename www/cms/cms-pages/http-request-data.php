<?php

require_once '../prepend.php';

$page = new App_Cms_Page();
$page->setRootName('http-request');
$page->setRootAttr('type', 'document-data');
$page->setTemplate(TEMPLATES . 'back/http-requests.xsl');

$data = $_POST;

if (!empty($data['id'])) {
    $page->setRootAttr('parent_id', $data['id']);
    $page->addContent(getBranchXml($data['id']));
}

$page->output();


function getBranchXml($_parentId)
{
    $result = '';
    $document = App_Cms_Front_Document::load($_parentId);

    foreach (
        App_Cms_Front_Data::getList(array(App_Cms_Front_Document::getPri() => $_parentId)) as
        $item
    ) {
        $xml = '';

        switch ($item->getTypeId()) {
            case 'image':
                if ($document && is_dir($document->getFilePath())) {
                    if ($document->getImages()) {
                        $xml .= '<self>';

                        foreach ($document->getImages() as $image) {
                            $xml .= $image->getXml();
                        }

                        $xml .= '</self>';
                    }
                }

                if (!isset($otherImages)) {
                    $otherImages = getDataImages(
                        DOCUMENT_ROOT . 'f/',
                        $document->getFilePath()
                    );
                }

                if ($otherImages) {
                    $xml .= '<others>';

                    foreach ($otherImages as $image) {
                        $xml .= $image->getXml();
                    }

                    $xml .= '</others>';
                }

                break;
        }

        $result .= $item->getXml($xml);
    }

    return $result;
}

function getDataImages($_dir, $_excludePath)
{
    $result = array();
    $dir = rtrim($_dir, '/') . '/';
    $excludePath = rtrim($_excludePath, '/') . '/';

    if (is_dir($dir)) {
        $dirHandle = opendir($dir);
        $item = readdir($dirHandle);

        while ($item) {
            if ($item != '.' && $item != '..') {
                if (is_dir($dir . $item)) {
                    $result = array_merge(
                        $result,
                        getDataImages($dir . $item, $excludePath)
                    );

                } else if ($dir != $excludePath && Ext_Image::IsImage($item)) {
                    $result[] = App_Image::factory($dir . $item);
                }
            }

            $item = readdir($dirHandle);
        }

        closedir($dirHandle);
    }

    return $result;
}
