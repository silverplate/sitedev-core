<?php

abstract class Core_Cms_Front_Data_Controller_SubpageNavigation
extends App_Cms_Front_Data_Controller
{
    /**
     * Expected content:
     * <except folder="send" />
     * <append filename="small" />
     */
    public function execute()
    {
        $content = $this->getContent();
        $except = array();
        $append = array();

        if ($content) {
            $pref = Ext_Dom::get(Core_Cms_Ext_Xml::getDocument($content));

            foreach ($pref->getElementsByTagName('except') as $item) {
                foreach ($item->attributes as $attr) {
                    if (!isset($except[$attr->name])) {
                        $except[$attr->name] = array();
                    }

                    $except[$attr->name][] = $attr->value;
                }
            }

            foreach ($pref->getElementsByTagName('append') as $item) {
                foreach ($item->attributes as $attr) {
                    if (!isset($append[$attr->name])) {
                        $append[$attr->name] = array();
                    }

                    $append[$attr->name][] = $attr->value;
                }
            }
        }

        $rowConds = array();
        $conds = array('parent_id' => $this->_parentDocument->getId(),
                       'is_published' => 1);

        foreach ($except as $attr => $value) {
            $rowConds[] = $attr . ' != ' . App_Db::escape($value);
        }

        $children = App_Cms_Front_Document::getList($conds, null, $rowConds);
        $xml = '';

        foreach ($children as $item) {
            $link = $item->link ? $item->link : $item->getUri();
            $itemXml = Ext_Xml::cdata('title', $item->getTitle());
            $itemAttrs = array('uri' => $item->getUri(), 'link' => $link);

            if (count($append) > 0) {
                foreach ($append as $type => $values) {
                    switch ($type) {
                        case 'filename':
                            foreach ($values as $value) {
                                $file = $item->getIllu($value);
                                if ($file) $itemXml .= $file->getXml();
                            }
                            break;
                    }
                }
            }

            $xml .= Ext_Xml::node('item', $itemXml, $itemAttrs);
        }

        $this->setContent($xml);
    }
}
