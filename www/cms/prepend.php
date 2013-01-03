<?php

require_once realpath(dirname(__FILE__) . '/../../../core/library') . '/libs.php';
require_once CORE_SETS . 'project.php';

$g_section_start_url = '/cms/';


/*** Authorization
*********************************************************/
if (isset($_POST['auth_submit'])) {
	$try = App_Cms_Back_User::Auth($_POST['auth_login'], $_POST['auth_password']);
	if ($try) {
		App_Cms_Session::Get()->Login(
			$try->GetId(),
			isset($_POST['auth_is_ip_match']),
			isset($_POST['auth_life_span']) ? (int) $_POST['auth_life_span'] : null,
			isset($_POST['auth_timeout']) ? (int) $_POST['auth_timeout'] : null,
			isset($_POST['auth_is_remember_me']) ? strtotime('+3 month') : null
		);

		App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_LOGIN);
		App_Cms_Back_Log::Log(App_Cms_Back_Log::ACT_LOGIN, array('user' => $try));

	} else {
		App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_LOGIN_ERROR);
	}

	reload(empty($_GET['id']) ? null : '?id=' . $_GET['id']);

} elseif (isset($_POST['auth_reminder_submit'])) {
	$try = isset($_POST['auth_email']) && $_POST['auth_email']
		? App_Cms_Back_User::GetList(array('email' => $_POST['auth_email'], 'status_id' => 1))
		: false;

	if ($try) {
		foreach ($try as $user) {
			App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_REMIND_PWD);
			App_Cms_Back_Log::Log(App_Cms_Back_Log::ACT_REMIND_PWD, array('user' => $user));
			$user->RemindPassword();
		}
	} else {
		App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_REMIND_PWD_ERROR);
	}

	reload();

} elseif (isset($_GET['r']) || (isset($_GET['e']) && App_Cms_Session::Get()->IsLoggedIn())) {
	if (App_Cms_Session::Get()->IsLoggedIn()) {
		App_Cms_Back_Log::Log(App_Cms_Back_Log::ACT_LOGOUT, array('user' => App_Cms_Back_User::Auth(App_Cms_Session::Get()->GetUserId())));
		App_Cms_Session::Get()->Logout();
	}

	if (isset($_GET['r'])) {
		$try = $_GET['r'] ? App_Cms_Back_User::Load($_GET['r'], 'reminder_key') : false;
		if ($try && $try->ChangePassword() == 0) {
			App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_CHANGE_PWD);
			App_Cms_Back_Log::Log(App_Cms_Back_Log::ACT_CHANGE_PWD, array('user' => $try));

		} else {
			App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_CHANGE_PWD_ERROR);
		}

	} else {
		App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_LOGOUT);
	}

	reload();

} else {
	App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM, App_Cms_Session::Get()->GetParam(App_Cms_Session::ACT_PARAM_NEXT) ? App_Cms_Session::Get()->GetParam(App_Cms_Session::ACT_PARAM_NEXT) : App_Cms_Session::ACT_START);
	App_Cms_Session::Get()->SetParam(App_Cms_Session::ACT_PARAM_NEXT, App_Cms_Session::ACT_CONTINUE);

	/* User */
	$g_user = App_Cms_Session::Get()->IsLoggedIn() ? App_Cms_Back_User::Auth(App_Cms_Session::Get()->GetUserId()) : false;

	/* Section */
	$g_section = App_Cms_Back_Section::compute();
}
