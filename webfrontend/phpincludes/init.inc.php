<?php
// start sessions
session_start();

// indlæs backend
require_once('../backend/backend.inc.php');

// indlæs frontend funktionerne
require_once('functions.inc.php');

// indlæs smarty template engine
require('smartylib/Smarty.class.php');
$smarty = new Smarty();
global $smarty;
$smarty->template_dir = WEBFRONTEND_PATH.'templates';
$smarty->compile_dir = WEBFRONTEND_PATH.'templates_c';
$smarty->cache_dir = WEBFRONTEND_PATH.'cache';
$smarty->config_dir = WEBFRONTEND_PATHFRONTEND_PATH.'configs';
$smarty->left_delimiter = "<!--{";
$smarty->right_delimiter = "}-->";

// standard template
define('WEBFRONTEND_MAINTEMPLATE', 'main.tpl');


// logged in variable
$logged_in = auth_is_logged_in();
$smarty->assign('logged_in', $logged_in);

// generer admin menu
if ($logged_in) {
	$adminmenu = null;
	// hvis bruger er medlem af netdrift
	if (auth_is_member_of('nyk_netdrift')) {
		$adminmenu[] = array('href'=>'admin-users.php', 'title'=>'User accounts');
		$adminmenu[] = array('href'=>'admin-groups.php', 'title'=>'Group memberships');
	}
	// ellers, hvis bruger er administrator af nogen gruppe
	elseif (sizeof(backend_hent_brugers_admin_medlemskaber($_SESSION['brugernavn']))>0)
		$adminmenu[] = array('href'=>'admin-groups.php', 'title'=>'Group memberships');

}
$smarty->assign('adminmenu', $adminmenu);

// set some other template variables
$smarty->assign('login_brugernavn', $_SESSION['brugernavn']);
