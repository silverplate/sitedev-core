<?php

namespace Core\Cms\Front;

abstract class Navigation extends \App\ActiveRecord
{
    /**
     * @var array[array]
     */
    protected static $_items = array();

    protected $_linkParams = array(
        'documents' => '\App\Cms\Front\Document\Has\Navigation'
    );

    public function __construct()
    {
        parent::__construct();

        $this->addPrimaryKey('integer');
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
        global $gSiteLangType;

        $list = \Ext\Db::get()->getList('
            SELECT
                d.' . \App\Cms\Front\Document::getPri() . ' AS id,
                d.*
            FROM
                ' . \App\Cms\Front\Navigation::getTbl() . ' AS n,
                ' . \App\Cms\Front\Document::getTbl() . ' AS d,
                ' . \App\Cms\Front\Document\Has\Navigation::getTbl() . ' AS l
            WHERE
                n.is_published = 1 AND
                n.name = ' . \Ext\Db::escape($_name) . ' AND
                n.' . \App\Cms\Front\Navigation::getPri() . ' = l.' . \App\Cms\Front\Navigation::getPri() . ' AND
                l.' . \App\Cms\Front\Document::getPri() . ' = d.' . \App\Cms\Front\Document::getPri() . ' AND
                d.is_published = 1' .
                (is_null(\App\Cms\User::getAuthGroup()) ? '' : ' AND (ISNULL(d.auth_status_id) OR d.auth_status_id = 0 OR d.auth_status_id & ' . \App\Cms\User::getAuthGroup() . ')') . '
            ORDER BY
                d.sort_order
        ');

        if ($list && \App\Cms\Front\Office::getLanguages()) {
            for ($i = 0; $i < count($list); $i++) {
                foreach (array_keys(\App\Cms\Front\Office::getLanguages()) as $j) {
                    $pos = strpos($list[$i]['uri'], "/$j/");

                    if (0 === $pos) {
                        $list[$i]['lang'] = $j;

                        if (
                            $gSiteLangType == 'host' ||
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
            $obj = new \App\Cms\Front\Document();
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

        return $result ? \Ext\Xml::node($_name, $result) : false;
    }

    public static function getNavigationXmlTree($_parentId = '')
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

                    $xml .= \Ext\Xml::node(
                        'item',
                        \Ext\Xml::cdata('title', $item['title_compact'] ? $item['title_compact'] : $item['title']) .
                        self::getNavigationXmlTree($item['id']),
                        $attrs
                    );
                }
            }
        }

        return $xml;
    }

    public static function getNavigationXmlList()
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

            $xml .= \Ext\Xml::node(
                'item',
                \Ext\Xml::cdata('title', $item['title_compact'] ? $item['title_compact'] : $item['title']),
                $attrs
            );
        }

        return $xml;
    }
}
