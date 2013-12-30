<?php

namespace Core\Helper;

abstract class SubpageNavigation extends \App\Cms\Front\Data\Controller
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
            $pref = \Ext\Xml\Dom::get(\Core\Cms\Ext\Xml::getDocument($content));

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
            $rowConds[] = $attr . ' != ' . \Ext\Db::escape($value);
        }

        $children = \App\Cms\Front\Document::getList($conds, null, $rowConds);
        $xml = '';

        foreach ($children as $item) {
            $link = $item->link ? $item->link : $item->getUri();
            $itemXml = \Ext\Xml::cdata('title', $item->getTitle());
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

            $xml .= \Ext\Xml::node('item', $itemXml, $itemAttrs);
        }

        $this->setContent($xml);
    }
}
