<?php

abstract class Core_Cms_Back_Office
{
    /**
     * Сортировка списка.
     *
     * @param string $_class Название класса объекты, которого нужно сортировать.
     */
    public static function ajaxSort($_class)
    {
        if (!empty($_POST['items'])) {
            $tmp = new $_class;
            $key = $_class->getPrimaryKeyName();

            $newSortOrder = array();
            for ($i = 0; $i < count($_POST['items']); $i++) {
                $newSortOrder[$_POST['items'][$i]] = $i;
            }

            $currentSortOrder = array();
            $objects = $_class::getList(array($key => $_POST['items']));

            foreach ($objects as $item) {
                $currentSortOrder[] = $item->sortOrder;
            }

            foreach ($objects as $item) {
                $newItemSortOrder = $currentSortOrder[$newSortOrder[$item->getId()]];

                if ($newItemSortOrder) {
                    $item->updateAttr('sort_order', $newItemSortOrder);
                }
            }

            App_Cms_Back_Log::logModule(App_Cms_Back_Log::ACT_MODIFY, null, 'Сортировка');
        }
    }

    public static function ajaxTreeOutput($_className)
    {
        global $gOpenBranches;

        $data = $_POST;
        $parentId = empty($data['parent_id']) ? null : $data['parent_id'];

        $selectedIds = isset($data['selected_ids']) &&
                       is_array($data['selected_ids'])
                     ? $data['selected_ids']
                     : array();

        $currentId = isset($data['current_object_id'])
                   ? $data['current_object_id']
                   : '';

        $page = new App_Cms_Page();
        $page->setTemplate(TEMPLATES . 'back/http-requests.xsl');
        $page->setRootName('http-request');

        if (isset($data['type'])) {
            $page->setRootAttr('type', 'tree_' . $data['type']);
        }

        if (isset($data['module_name'])) {
            $page->setRootAttr('module-name', $data['module_name']);
        }

        if (isset($data['field_name'])) {
            $page->setRootAttr('field-name', $data['field_name']);
        }

        if ($parentId) {
            $page->setRootAttr('parent-id', $parentId);
        }

        if ($currentId) {
            $page->setRootAttr('current-object-id', $currentId);
        }

        foreach ($selectedIds as $i) {
            $page->addContent(Ext_Xml::cdata('selected', $i));
        }

        $gOpenBranches = $selectedIds
                       ? $_className::getMultiAncestors($selectedIds)
                       : array();

        $cookieBranchName = 'bo-tree';

        if (isset($data['module_name'])) {
            $cookieBranchName .= '-' . $data['module_name'];
        }

        if (isset($data['field_name'])) {
            $cookieBranchName .= '-' . $data['field_name'];
        }

        if (!empty($_COOKIE[$cookieBranchName])) {
            foreach (explode('|', $_COOKIE[$cookieBranchName]) as $item) {
                if (!in_array($item, $gOpenBranches)) {
                    $gOpenBranches[] = $item;
                }
            }
        }

        if (isset($data['type']) && $data['type'] == 'single' && !$parentId) {
            $obj = new $_className;
            $obj->isPublished = 1;

            if ('integer' == $obj->getPrimaryKey()->getType()) {
                $obj->id = 0;
            }

            if ($obj->hasAttribute('title')) {
                $obj->title = 'Нет';

            } else if ($obj->hasAttribute('name')) {
                $obj->name = 'Нет';
            }

            $xml = $obj->getBackOfficeXml(
                self::ajaxGetBranchXml($_className, $parentId, $currentId)
            );

        } else {
            $xml = self::ajaxGetBranchXml($_className, $parentId, $currentId);
        }

        $page->addContent($xml);

        header('Content-type: text/html; charset=utf-8');
        $page->output();
    }

    public static function ajaxGetBranchXml($_className, $_parentId, $_excludeId)
    {
        global $gOpenBranches;

        $result = '';
        $where = array('parent_id' => empty($_parentId) ? null : $_parentId);

        if ($_excludeId) {
            $where[] = $_className::getPri() . ' != ' . App_Db::escape($_excludeId);
        }

        $list = $_className::getList($where);

        foreach ($list as $item) {
            if (
                $item->isChildren($_excludeId) &&
                in_array($item->getId(), $gOpenBranches)
            ) {
                $result .= $item->getBackOfficeXml(
                    self::ajaxGetBranchXml($_className, $item->getId(), $_excludeId)
                );

            } else if ($item->isChildren($_excludeId)) {
                $result .= $item->getBackOfficeXml(null, array('has-children' => 'true'));

            } else {
                $result .= $item->getBackOfficeXml();
            }
        }

        return $result;
    }
}
