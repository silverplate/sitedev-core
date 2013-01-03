<?php

class Core_Cms_Ext_Xml extends Ext_Xml
{
    public static function getEntitiesPath()
    {
        return CORE_TEMPLATES . 'entities.dtd';
    }

    public static function getHead($_root = 'root')
    {
        return parent::getHead(self::getEntitiesPath(), $_root);
    }

    public static function getDocument($_xml, $_attrs = null, $_root = 'root')
    {
        return parent::getDocument($_xml, $_attrs, $_root, self::getEntitiesPath());
    }

    public static function getDocumentForXml($_xml, $_root = 'root')
    {
        return parent::getDocumentForXml($_xml, $_root, self::getEntitiesPath());
    }

    /**
     * @param string $_xml
     * @param array[Ext_File] $_files
     * @param array $_match
     * @return string
     */
    public static function applyFiles($_xml, array $_files, array $_match = null)
    {
        $dom = Ext_Dom::get(self::getDocument($_xml));
        $match = empty($_match) ? array('illu', 'image', 'file') : $_match;

        if (count($match) > 1) {
            $xpath = new DOMXPath($dom);
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
                    $filePath = Ext_File::getByName(
                        rtrim(DOCUMENT_ROOT, '/') . $node->getAttribute('alias-uri'),
                        $alias
                    );

                    if ($filePath) {
                        $file = App_Image::factory($filePath);
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
                    $file = App_Image::factory($filePath);
                }
            }

            if (!empty($file)) {
                $node->parentNode->replaceChild($file->getNode($dom), $node);
            }
        }

        return Ext_Dom::getInnerXml($dom);
    }
}
