<?php

abstract class Core_Cms_Front_Document_Controller extends Core_Cms_Front_Page
{
    /**
     * @var Core_Cms_Front_Document
     */
    protected $Document;

    public function __construct(&$_document) {
        parent::__construct();
        $this->Document = $_document;
    }

    public function Execute() {
        if ($this->Document) {
            if (!$this->GetTitle()) {
                $this->SetTitle($this->Document->GetTitle());
            }

            if ($this->Document->GetLang()) {
                $this->SetRootNodeAttribute('xml:lang', $this->Document->GetLang());
            }

            $where = array('is_published' => 1);

            $ancestors = App_Cms_Front_Document::GetAncestors($this->Document->GetId());
            if ($ancestors) $ancestors = array_values(array_diff($ancestors, array($this->Document->GetId())));

            if ($ancestors) {
                $where[] =
                    '((' . App_Cms_Front_Document::GetPri() . ' IN (' . App_Db::escape($ancestors) . ') AND apply_type_id IN (2, 3)) OR (' .
                    App_Cms_Front_Document::GetPri() . ' = ' . App_Db::escape($this->Document->GetId()) . ' AND apply_type_id IN (1, 3)))';

            } else {
                $where[] = '(' . App_Cms_Front_Document::GetPri() . ' = ' . App_Db::escape($this->Document->GetId()) . ' AND apply_type_id IN (1, 3))';
            }

            if (!is_null(App_Cms_User::GetAuthGroup())) {
                $where[] = '(auth_status_id = 0 OR auth_status_id & ' . App_Cms_User::GetAuthGroup() . ')';
            }

            $data = App_Cms_Front_Data::getList($where);
            $dataXml = array();

            foreach ($data as $item) {
                switch ($item->GetTypeId()) {
                    case 'image':
                        if ($item->content) {
                            if (strpos($item->content, '://') !== true) {
                                $image_file_path = DOCUMENT_ROOT . ltrim($item->content, '/');
                                if (is_file($image_file_path)) {
                                    $image_file = App_File::factory($image_file_path);

                                    if ($image_file->isImage()) {
                                        $item->content = $image_file->getXml();
                                        $item->setTypeId('xml');
                                    }
                                }
                            }
                        }
                        break;

                    case 'xml':
                        if ($item->content) {
                            $data_dom = Ext_Dom::get(Core_Cms_Ext_Xml::getDocument(
                                $item->content,
                                'data'
                            ));

                            $data_images = $data_dom->getElementsByTagName('image');

                            foreach ($data_images as $data_image) {
                                if ($data_image->hasAttribute('alias')) {
                                    $image = null;

                                    if ($data_image->hasAttribute('alias-uri')) {
                                        $filepath = Ext_File::getByName(
                                            rtrim(DOCUMENT_ROOT, '/') . $data_image->getAttribute('alias-uri'),
                                            $data_image->getAttribute('alias')
                                        );

                                        if ($filepath) {
                                            $image = App_Image::factory($filepath);
                                        }

                                    } else {
                                        $image = $this->Document->getIlluByName($data_image->getAttribute('alias'));
                                    }

                                    if (empty($image)) continue;
                                    $data_image->parentNode->replaceChild($image->getNode($data_dom), $data_image);
                                }
                            }

                            $data_files = $data_dom->getElementsByTagName('file');
                            foreach ($data_files as $data_file) {
                                if ($data_file->hasAttribute('alias')) {
                                    if ($data_file->hasAttribute('alias-uri')) {
                                        $filepath = Ext_File::getByName(
                                            rtrim(DOCUMENT_ROOT, '/') . $data_file->getAttribute('alias-uri'),
                                            $data_file->getAttribute('alias')
                                        );

                                        if ($filepath) {
                                            $file = App_File::factory($file);
                                        }

                                    } else {
                                        $file = $this->Document->getFileByName($data_file->getAttribute('alias'));
                                    }

                                    if (empty($file)) continue;
                                    $data_file->parentNode->replaceChild($file->getNode($data_dom), $data_file);
                                }
                            }

                            $content = '';
                            foreach ($data_dom->documentElement->childNodes as $child) {
                                $content .= $data_dom->saveXml($child);
                            }

                            $item->content = $content;
                        }
                        break;
                }

                if ($item->getControllerFile()) {
                    $controller = App_Cms_Front_Data::initController(
                        $item->getController(),
                        $item,
                        $this->Document
                    );

                    $controller->execute();
                    $dataXml[] = $controller->getXml();

                } else {
                    $plainData = new App_Cms_Front_Data_Controller($item);
                    $dataXml[] = $plainData->getXml();
                }
            }

            $content = array();
            foreach ($dataXml as $xml) {
                $content[] = $xml;
            }

            $this->setContent(array_merge($content, $this->getContent()));
        }
    }
}