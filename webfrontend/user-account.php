<?php
try {
	require_once('phpincludes/init.inc.php');
	auth_require_login();

	$brugere = backend_hent_brugere(array('brugernavn'=>$_SESSION['brugernavn']));
	if (sizeof($brugere)==0)
		die('User not found.');
	$brugerdata = $brugere[0];

	if ($_POST['action']=='save') {
		$formdata = array('email'=>$_POST['email'], 'mobilnummer'=>$_POST['mobilnummer'], 'hjemmeside'=>$_POST['hjemmeside'], 'skjult_navn'=>(bool)$_POST['skjult_navn'], 'skjult_email'=>(bool)$_POST['skjult_email']);
	
		// valider inputs
		$validate_errors = array();
		if(!validate_email($formdata['email'])) $validate_errors[] = 'Invalid email address.';
		if (!empty($_POST['newpassword']) && $_POST['newpassword']!=$_POST['newpassword2']) $validate_errors[] = 'The two password fields is not equal.';
		if (sizeof($validate_errors)>0) {
			$smarty->assign('errors', $validate_errors);
		}
		else {
			// ingen valideringsfejl, så opdater data
			// hvis passwordfeltet er udfyldt, sendes dette med de øvrige data i backend_set_brugerdata
			if (!empty($_POST['newpassword'])) $formdata['password'] = $_POST['newpassword'];
			backend_set_brugerdata($_SESSION['brugernavn'], $formdata);

			backend_opdater_datafiler();
			$smarty->assign('messages', array('The account data is updated.'));
		}	
	}
	else {
		$formdata = array('email'=>$brugerdata['email'], 'mobilnummer'=>$brugerdata['mobilnummer'], 'hjemmeside'=>$brugerdata['hjemmeside'], 'skjult_navn'=>(bool)$brugerdata['skjult_navn'], 'skjult_email'=>(bool)$brugerdata['skjult_email']);
	}

	$smarty->assign('brugerdata', $brugerdata);
	$smarty->assign('formdata', $formdata);
	$smarty->assign('contenttemplate', 'user-account.tpl');
}
catch(Exception $e) {
	fatal_error($e);	
}

$smarty->display(WEBFRONTEND_MAINTEMPLATE); 
