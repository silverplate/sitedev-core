<?php

function boLogFilter($_filter)
{
    $where = array(
        'from_date' => $_filter['from_date'],
        'till_date' => $_filter['till_date']
    );

    $params = array(
        'limit' => $_filter['per_page'],
        'offset' => (((int) $_filter['page'] ? $_filter['page'] : 1) - 1) *
                    $_filter['per_page']
    );

    if ($_filter['is_users']) {
        $instance = new App_Cms_Back_User();

        if ($_filter['users']) {
            $where[$instance->getPrimaryKeyName()] = $_filter['users'];

        } else {
            $where[] = $instance->getPrimaryKeyName() .
                       ' NOT IN (' . Ext_Db::escape(array_keys(App_Cms_Back_User::getList())) . ')';
        }
    }

    if ($_filter['is_sections']) {
        $instance = new App_Cms_Back_Section();

        if ($_filter['sections']) {
            $where[$instance->getPrimaryKeyName()] = $_filter['sections'];

        } else {
            $where[] = $instance->getPrimaryKeyName() .
                       ' NOT IN (' . Ext_Db::escape(array_keys(App_Cms_Back_Section::getList())) . ')';
        }
    }

    if ($_filter['is_actions']) {
        if ($_filter['actions']) {
            $where['action_id'] = $_filter['actions'];

        } else {
            $where[] = 'action_id NOT IN (' . Ext_Db::escape(array_keys(App_Cms_Back_Log::getActions())) . ')';
        }
    }

    return array(
        'items' => App_Cms_Back_Log::getList($where, $params),
        'total' => App_Cms_Back_Log::getCount($where)
    );
}

function boLogGetFilter()
{
    $result = array('per_page' => 10, 'is_open' => !empty($_COOKIE['filter-is-open']));

    if (
        !empty($_POST['filter_from']) &&
        strtotime($_POST['filter_from'])
    ) {
        $result['from_date'] = strtotime($_POST['filter_from']);

    } else if (
        !empty($_COOKIE['filter-from']) &&
        strtotime($_COOKIE['filter-from'])
    ) {
        $result['from_date'] = strtotime($_COOKIE['filter-from']);

    } else {
        $result['from_date'] = time();
    }

    if (
        !empty($_POST['filter_till']) &&
        strtotime($_POST['filter_till'])
    ) {
        $result['till_date'] = strtotime($_POST['filter_till']);

    } else if (
        !empty($_COOKIE['filter-till']) &&
        strtotime($_COOKIE['filter-till'])
    ) {
        $result['till_date'] = strtotime($_COOKIE['filter-till']);

    } else {
        $result['till_date'] = time();
    }

    foreach (array('users', 'sections', 'actions') as $item) {
        $result['is_' . $item] = false;
        $result[$item] = false;

        if ($_POST) {
            if (!empty($_POST["is_filter_$item"])) {
                $result["is_$item"] = true;
                $result[$item] = !empty($_POST["filter_$item"])
                               ? $_POST["filter_$item"]
                               : false;
            }

        } else if (!empty($_COOKIE['is-filter-' . $item])) {
            $result["is_$item"] = true;
            $result[$item] = !empty($_COOKIE["filter-$item"])
                           ? explode('|', preg_replace('/%u([0-9A-F]{4})/se', 'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))', $_COOKIE["filter-$item"]))
                           : false;
        }
    }

    if (isset($_POST['page']) && (int) $_POST['page']) {
        $result['page'] = (int) $_POST['page'];

    } else if (
        isset($_COOKIE['filter-page']) &&
        (int) $_COOKIE['filter-page']
    ) {
        $result['page'] = (int) $_COOKIE['filter-page'];

    } else {
        $result['page'] = 1;
    }

    return $result;
}
