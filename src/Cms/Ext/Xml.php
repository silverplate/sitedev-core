<?php

namespace Core\Cms\Ext;

abstract class Xml extends \Ext\Xml
{
    /**
     * @param string $_xml
     * @param \Ext\File[] $_files
     * @param array $_match
     * @return string
     */
    public static function applyFiles($_xml, array $_files, array $_match = null)
    {
        $dom = \Ext\Xml\Dom::get(self::getDocument($_xml));
        $match = empty($_match) ? array('illu', 'image', 'file') : $_match;

        if (count($match) > 1) {
            $xpath = new \DOMXPath($dom);
            $query = array();

            foreach ($match as $item) {
                $query[] = "name() = '$item'";
            }

            $items = $xpath->query('//node()[' . implode(' or ', $query) . ']');

        } else {
            $items = $dom->getElementsByTagName($match[0]);
        }

        foreach ($items as $node) {
            $file = null;

            if ($node->hasAttribute('alias')) {
                $alias = $node->getAttribute('alias');

                if ($node->hasAttribute('alias-uri')) {
                    $filePath = \Ext\File::getByName(
                        rtrim(DOCUMENT_ROOT, '/') . $node->getAttribute('alias-uri'),
                        $alias
                    );

                    if ($filePath) {
                        $file = \App\Cms\Ext\Image::factory($filePath);
                    }

                } else {
                    foreach ($_files as $try) {
                        if (
                            $try->getFilename() == $alias ||
                            $try->getName() == $alias
                        ) {
                            $file = $try;
                            break;
                        }
                    }
                }

            } else if (
                $node->hasAttribute('uri') &&
                !$node->hasAttribute('width')
            ) {
                $filePath = rtrim(DOCUMENT_ROOT, '/') . $node->getAttribute('uri');

                if (is_file($filePath)) {
                    $file = \App\Cms\Ext\Image::factory($filePath);
                }
            }

            if (!empty($file)) {
                $node->parentNode->replaceChild($file->getNode($dom), $node);
            }
        }

        return \Ext\Xml\Dom::getInnerXml($dom->documentElement);
    }
}
