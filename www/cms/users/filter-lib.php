<?php

function obj_filter($_filter) {
	$row_conditions = array();
	$page = (int) $_filter['page'] ? $_filter['page'] : 1;
	$parameters = array('count' => $_filter['per_page'], 'offset' => ($page - 1) * $_filter['per_page']);

	if ($_filter['name']) {
		array_push($row_conditions, 'CONCAT(last_name, " ", first_name, " ", patronymic_name) LIKE "%' . $_filter['name'] . '%"');
	}

	if ($_filter['email']) {
		array_push($row_conditions, 'email LIKE "%' . $_filter['email'] . '%"');
	}

	return array(
		'items' => App_Cms_User::GetList(null, $parameters, $row_conditions),
		'total' => App_Cms_User::GetCount(null, $row_conditions)
	);
}

function obj_get_filter($_is_parse_post = false) {
	if ($_is_parse_post && $_POST) {
		foreach ($_POST as $key => $value) {
			if (is_array($value)) {
// 				array_walk($_POST[$key], 'decode_array');
				$_POST[$key] = $_POST[$key];
			} else {
				$_POST[$key] = $value;
			}
		}
	}

	$result = array(
		'per_page' => 25,
		'selected_id' => isset($_POST['filter_selected_id']) ? $_POST['filter_selected_id'] : false,
		'is_open' => isset($_COOKIE['filter_is_open']) && $_COOKIE['filter_is_open'],
		'is_name' => true,
		'is_email' => true
	);

	foreach (array('name', 'email') as $item) {
		if (isset($_POST['filter_' . $item]) && $_POST['filter_' . $item]) {
			$result[$item] = $_POST['filter_' . $item];
		} elseif (isset($_COOKIE['filter_' . $item]) && $_COOKIE['filter_' . $item]) {
			$result[$item] = preg_replace('/%u([0-9A-F]{4})/se', 'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))', $_COOKIE['filter_' . $item]);
		} else {
			$result[$item] = false;
		}
	}

	if (isset($_POST['page']) && (int) $_POST['page']) {
		$result['page'] = $_POST['page'];
	} elseif (isset($_COOKIE['filter_page']) && (int) $_COOKIE['filter_page']) {
		$result['page'] = $_COOKIE['filter_page'];
	} else {
		$result['page'] = 1;
	}

	return $result;
}

?>