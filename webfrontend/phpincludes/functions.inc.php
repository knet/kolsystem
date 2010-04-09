<?php

function fatal_error($e=null) {
	// log fejlen
	$logstring = date('D, d M Y H:i:s').' ';
	if($_SESSION['brugernavn'])
		$logstring .= 'username='.$_SESSION['brugernavn'].', ';		
	$logstring .= 'page='.$_SERVER['SCRIPT_FILENAME'].', ';
	$logstring .= 'client_ip='.$_SERVER['REMOTE_ADDR'].', ';
	if($e) {
		$e_msg = $e->getTraceAsString();
		$e_msg = str_replace("\r\n", ', ', $e_msg);
		$e_msg = str_replace("\n", ', ', $e_msg);
		$e_msg = str_replace("\r", ', ', $e_msg);
		$logstring .= 'exception='.$e_msg.', ';
	}

	$logstring = trim($logstring, ',');
	$logstring = trim($logstring);
	$logstring = preg_replace('/password.*/', '...', $logstring);

	@file_put_contents(WEBFRONTEND_LOGFILE, $logstring."\n", FILE_APPEND);
	
	// vis fejlside
	global $smarty;
	$smarty->assign('contenttemplate', 'error.tpl');	
	$smarty->display(WEBFRONTEND_MAINTEMPLATE);
	
	
	// force exit
	exit();
}


function validate_email($email) {
	return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
}


/* LOGIN FUNKTIONER (starter alle med auth) */

function auth_login($brugernavn, $password) {
	if (backend_valider_bruger_og_password($brugernavn, $password)) {
		$_SESSION['brugernavn'] = $brugernavn;
		return true;
	}
	sleep(2);
	return false;
}

function auth_logout() {
	$_SESSION['brugernavn'] = '';
}

function auth_is_logged_in() {
	return !empty($_SESSION['brugernavn']);		
}

function auth_is_member_of($groupname) {
	try {
		foreach(backend_hent_brugers_medlemskaber($_SESSION['brugernavn']) as $group) {
			if($group['gruppenavn']==$groupname) return true;
		}
	} catch(DatabaseException $e) {
	} catch(UkendtBrugerException $e) {
		auth_logout();
		header('location: login.php');
		die('User not found.');
	}
	return false;		
}

function auth_require_login() {
	if (!auth_is_logged_in()) {
		header('location: login.php');
		die('No access');
	}
}

function auth_require_member_of($groupname) {
	if (!auth_is_member_of($groupname)) {
		header('location: login.php');
		die('No access');
	}
}
