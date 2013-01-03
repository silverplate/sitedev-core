<?php

function bo_log_filter($_filter) {
	$conditions = array();
	$row_conditions = array();

	$conditions['from_date'] = $_filter['from_date'];
	$conditions['till_date'] = $_filter['till_date'];

	$page = (int) $_filter['page'] ? $_filter['page'] : 1;
	$parameters = array('count' => $_filter['per_page'], 'offset' => ($page - 1) * $_filter['per_page']);

	if ($_filter['is_users']) {
		if ($_filter['users'] && is_array($_filter['users'])) {
			array_push($row_conditions, App_Cms_Back_User::GetPri() . ' IN (' . App_Db::escape($_filter['users']) . ')');
		} else {
			array_push($row_conditions, App_Cms_Back_User::GetPri() . ' NOT IN (' . App_Db::escape(array_keys(App_Cms_Back_User::GetList())) . ')');
		}
	}

	if ($_filter['is_sections']) {
		if ($_filter['sections'] && is_array($_filter['sections'])) {
			array_push($row_conditions, App_Cms_Back_Section::GetPri() . ' IN (' . App_Db::escape($_filter['sections']) . ')');
		} else {
			array_push($row_conditions, App_Cms_Back_Section::GetPri() . ' NOT IN (' . App_Db::escape(array_keys(App_Cms_Back_Section::GetList())) . ')');
		}
	}

	if ($_filter['is_actions']) {
		if ($_filter['actions'] && is_array($_filter['actions'])) {
			array_push($row_conditions, 'action_id IN (' . App_Db::escape($_filter['actions']) . ')');
		} else {
			array_push($row_conditions, 'action_id NOT IN (' . App_Db::escape(array_keys(App_Cms_Back_Log::GetActions())) . ')');
		}
	}

	return array(
		'items' => App_Cms_Back_Log::GetList($conditions, $parameters, $row_conditions),
		'total' => App_Cms_Back_Log::GetCount($conditions, $row_conditions)
	);
}

function bo_log_get_filter($_is_parse_post = false) {
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
		'per_page' => 10,
		'is_open' => isset($_COOKIE['filter_is_open']) && $_COOKIE['filter_is_open']
	);

	if (isset($_POST['filter_from']) && $_POST['filter_from'] && strtotime($_POST['filter_from'])) {
		$result['from_date'] = strtotime($_POST['filter_from']);
	} elseif (isset($_COOKIE['filter_from']) && $_COOKIE['filter_from'] && strtotime($_COOKIE['filter_from'])) {
		$result['from_date'] = strtotime($_COOKIE['filter_from']);
	} else {
		$result['from_date'] = mktime();
	}

	if (isset($_POST['filter_till']) && $_POST['filter_till'] && strtotime($_POST['filter_till'])) {
		$result['till_date'] = strtotime($_POST['filter_till']);
	} elseif (isset($_COOKIE['filter_till']) && $_COOKIE['filter_till'] && strtotime($_COOKIE['filter_till'])) {
		$result['till_date'] = strtotime($_COOKIE['filter_till']);
	} else {
		$result['till_date'] = mktime();
	}

	foreach (array('users', 'sections', 'actions') as $item) {
		$result['is_' . $item] = false;
		$result[$item] = false;

		if ($_POST) {
			if (isset($_POST['is_filter_' . $item]) && $_POST['is_filter_' . $item] == 1) {
				$result['is_' . $item] = true;
				$result[$item] = isset($_POST['filter_' . $item]) && $_POST['filter_' . $item] ? $_POST['filter_' . $item] : false;
			}
		} elseif (isset($_COOKIE['is_filter_' . $item]) && $_COOKIE['is_filter_' . $item] == 1) {
			$result['is_' . $item] = true;
			$result[$item] = $_COOKIE['filter_' . $item]
				? explode('|', preg_replace('/%u([0-9A-F]{4})/se', 'iconv("UTF-16BE", "utf-8", pack("H4", "$1"))', $_COOKIE['filter_' . $item]))
				: false;
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
