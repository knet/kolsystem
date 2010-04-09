<?php
try {
	require_once('phpincludes/init.inc.php');
	// hvis bruger er logget ind, send videre til index.php
	if (auth_is_logged_in()) {
		header('location: index.php');
	}

	// hvis formular udfyldt
	if (isset($_POST['brugernavn'])) {
		$input = array('brugernavn'=>$_POST['brugernavn'], 'password'=>$_POST['password']);
		if(auth_login($input['brugernavn'], $input['password'])) {
			// hvis login success, send brugere videre til index.php
			header('location: index.php');
		}
		else {
			$smarty->assign('error', 'Wrong username or password');
		}
		$smarty->assign('input', $input);
	}

	$smarty->assign('set_focus', 'true');
	$smarty->assign('contenttemplate', 'login.tpl');
}
catch(Exception $e) {
	fatal_error($e);	
}



$smarty->display(WEBFRONTEND_MAINTEMPLATE); 
