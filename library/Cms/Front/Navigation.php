<?php

abstract class Core_Cms_Front_Navigation extends App_Model
{
    /**
     * @var array[array]
     */
    protected static $_items = array();

    protected $_linkParams = array(
        'documents' => 'App_Cms_Front_Document_Has_Navigation'
    );

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('string');
        $this->addAttr('name', 'string');
        $this->addAttr('type', 'string');
        $this->addAttr('title', 'string');
        $this->addAttr('is_published', 'boolean');
        $this->addAttr('sort_order', 'integer');
    }

    public static function getTypes()
    {
        return array(
            'list' => array('title' => 'Список'),
            'tree' => array('title' => 'Дерево')
        );
    }

    public static function getRowDocuments($_name)
    {
        global $g_langs;

        $list = App_Db::get()->getList('
            SELECT
                d.' . App_Cms_Front_Document::getPri() . ' AS id,
                d.*
            FROM
                ' . App_Cms_Front_Navigation::getTbl() . ' AS n,
                ' . App_Cms_Front_Document::getTbl() . ' AS d,
                ' . App_Cms_Front_Document_Has_Navigation::getTbl() . ' AS l
            WHERE
                n.is_published = 1 AND
                n.name = ' . App_Db::escape($_name) . ' AND
                n.' . App_Cms_Front_Navigation::getPri() . ' = l.' . App_Cms_Front_Navigation::getPri() . ' AND
                l.' . App_Cms_Front_Document::getPri() . ' = d.' . App_Cms_Front_Document::getPri() . ' AND
                d.is_published = 1' .
                (is_null(App_Cms_User::getAuthGroup()) ? '' : ' AND (d.auth_status_id = 0 OR d.auth_status_id & ' . App_Cms_User::getAuthGroup() . ')') . '
            ORDER BY
                d.sort_order
        ');

        if ($list && !empty($g_langs)) {
            for ($i = 0; $i < count($list); $i++) {
                foreach (array_keys($g_langs) as $j) {
                    $pos = strpos($list[$i]['uri'], "/$j/");

                    if (0 === $pos) {
                        $list[$i]['lang'] = $j;

                        if (
                            'host' == SITE_LANG_TYPE ||
                            0 != strpos($list[$i]['uri'], "/$j/")
                        ) {
                            $list[$i]['uri'] = substr($list[$i]['uri'], strlen($j) + 2 - 1);
                        }

                        break;
                    }
                }
            }
        }

        return $list;
    }

    public static function getDocuments($_name)
    {
        $documents = array();
        $data = self::getRowDocuments($_name);

        foreach ($data as $row) {
            $obj = new App_Cms_Front_Document();
            $obj->fillWithData($row);

            $documents[$obj->id] = $obj;
        }

        return $documents;
    }

    public static function getNavigationXml($_name, $_type)
    {
        self::$_items = self::getRowDocuments($_name);

        $result = $_type == 'tree'
                ? self::getNavigationXmlTree()
                : self::getNavigationXmlList();

        return $result ? Ext_Xml::node($_name, $result) : false;
    }

    public function getNavigationXmlTree($_parentId = '')
    {
        $xml = '';

        foreach (array_keys(self::$_items) as $key) {
            if (isset(self::$_items[$key])) {
                $item = self::$_items[$key];

                if ($item['parent_id'] == $_parentId) {
                    unset(self::$_items[$key]);

                    $attrs = array(
                        'uri' => $item['uri'],
                        'link' => $item['link'] ? $item['link'] : $item['uri']
                    );

                    if (isset($item['lang'])) {
                        $attrs['xml:lang'] = $item['lang'];
                    }

                    $xml .= Ext_Xml::node(
                        'item',
                        Ext_Xml::cdata('title', $item['title_compact'] ? $item['title_compact'] : $item['title']) .
                        self::getNavigationXmlTree($item['id']),
                        $attrs
                    );
                }
            }
        }

        return $xml;
    }

    public function getNavigationXmlList()
    {
        $xml = '';

        foreach (self::$_items as $item) {
            $attrs = array(
                'uri' => $item['uri'],
                'link' => $item['link'] ? $item['link'] : $item['uri']
            );

            if (isset($item['lang'])) {
                $attrs['xml:lang'] = $item['lang'];
            }

            $xml .= Ext_Xml::node(
                'item',
                Ext_Xml::cdata('title', $item['title_compact'] ? $item['title_compact'] : $item['title']),
                $attrs
            );
        }

        return $xml;
    }
}
