<?php

require_once realpath(dirname(__FILE__) . '/../../core/library') . '/libs.php';
require_once CORE_SETS . 'project.php';


/**
 * Language
 */

$site_lang_type = '';
$site_lang = null;

global $g_langs;

$host = isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != ''
	? $_SERVER['HTTP_HOST']
	: false;

$url = isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != ''
	? parse_url($_SERVER['REQUEST_URI'])
	: false;

if ($host !== false && $url !== false && isset($g_langs)) {
	$lang_path = array();
	foreach ($g_langs as $lang => $params) {
		foreach (Ext_String::split($params[0]) as $item) {
			if (
				$host == $item || (
					'/' == $item &&
					'/' == $url['path']
				) || (
					'/' != $item &&
					strpos($url['path'], $item) === 0
				)
			) {
				$local_lang_path = explode('/', trim($item, '/'));
				if (count($lang_path) < count($local_lang_path)) {
					$site_lang = $lang;
					$lang_path = $local_lang_path;
					$site_lang_type = $host == $item ? 'host' : 'path';
				}
			}
		}
	}
	reset($g_langs);
}

define('SITE_LANG_TYPE', $site_lang_type);
define(
	'SITE_LANG',
	$site_lang ? $site_lang : (isset($g_langs) && $g_langs ? key($g_langs) : '')
);

unset($site_lang, $host, $lang, $params, $lang_path, $local_lang_path);


/**
 * Administration
 */

$g_admin = array('params' => array('mode' => 'is_admin_mode', 'hidden' => 'is_hidden'));
define('IS_KEY', isset($_GET['key']) && $_GET['key'] == SITE_KEY);
define('IS_ADMIN_MODE', get_admin_param('mode'));

if (IS_KEY) {
	set_admin_param($g_admin['params']['mode'], true);
	set_admin_param($g_admin['params']['hidden'], true);
	goToUrl('./' . (isset($_GET['xml']) ? '?xml' : ''));

} elseif (IS_ADMIN_MODE) {
	$g_admin['is_delete_cache'] = isset($_GET['delete_cache']);
}

define('IS_HIDDEN', IS_ADMIN_MODE && get_admin_param('hidden'));


function get_admin_param($_name) {
	global $g_admin;
	return isset($_COOKIE[$g_admin['params'][$_name]]) && $_COOKIE[$g_admin['params'][$_name]];
}

function set_admin_param($_name, $_is_on) {
	if ($_is_on) {
		setcookie($_name, 1, null, '/');
		$_COOKIE[$_name] = 1;

	} else {
		setcookie($_name, null, null, '/');
		unset($_COOKIE[$_name]);
	}
}


/**
 * Authorization
 */

if (defined('IS_USERS') && IS_USERS) {
    App_Cms_User::StartSession();
}
