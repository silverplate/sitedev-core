<?php

function objFilter($_filter)
{
    $where = array();
    $page = (int) $_filter['page'] ? $_filter['page'] : 1;
    $params = array(
        'limit' => $_filter['per_page'],
        'offset' => ($page - 1) * $_filter['per_page']
    );

    if (!empty($_filter['name'])) {
        $where[] = 'CONCAT_WS(" ", last_name, first_name, middle_name) LIKE "%' .
                   $_filter['name'] . '%"';
    }

    if (!empty($_filter['email'])) {
        $where[] = 'email LIKE "%' . $_filter['email'] . '%"';
    }

    return array(
        'items' => App_Cms_User::getList($where, $params),
        'total' => App_Cms_User::getCount($where)
    );
}

function objGetFilter()
{
    $result = array(
        'per_page' => 25,
        'selected_id' => empty($_POST['filter_selected_id']) ? false : $_POST['filter_selected_id'],
        'is_open' => !empty($_COOKIE['filter-is-open']),
        'is_name' => true,
        'is_email' => true
    );

    foreach (array('name', 'email') as $item) {
        if (!empty($_POST["filter_$item"])) {
            $result[$item] = $_POST["filter_$item"];

        } else if (!empty($_COOKIE["filter-$item"])) {
            $result[$item] = preg_replace(
                '/%u([0-9A-F]{4})/se',
                'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))',
                $_COOKIE["filter-$item"]
            );

        } else {
            $result[$item] = false;
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
