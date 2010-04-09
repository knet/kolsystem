<?php
try {
	require_once('phpincludes/init.inc.php');
	auth_require_login();
	auth_require_member_of('nyk_netdrift');

	$brugernavn = $_REQUEST['username'];
	$bruger_exists = sizeof(backend_hent_brugere(array('brugernavn'=>$brugernavn)))==1;

	if(!$bruger_exists) {
		// vis brugersøgning
		$smarty->assign('include_autocomplete', true);
		$smarty->assign('contenttemplate', 'admin-users-finduser.tpl');
		$smarty->assign('set_focus', 'true');
	}
	else { 	// vis formular

		// hvis data er sendt
		if($_POST['action']) {
			$formdata = array('email'=>$_POST['email'], 'mobilnummer'=>$_POST['mobilnummer'], 'hjemmeside'=>$_POST['hjemmeside'], 'skjult_navn'=>(bool)$_POST['skjult_navn'], 'skjult_email'=>(bool)$_POST['skjult_email'], 'action'=>$_POST['action']);
			
		
			// valider inputs
			$validate_errors = array();
			if (sizeof($validate_errors)>0) {
				$smarty->assign('errors', $validate_errors);
			}
			else {
				// ingen valideringsfejl, så opdater data
				// email og mobilnummer skal opdateres
				$setbrugerdata = array('email'=>$formdata['email'], 'mobilnummer'=>$formdata['mobilnummer'], 'hjemmeside'=>$formdata['hjemmeside'], 'skjult_navn'=>$formdata['skjult_navn'], 'skjult_email'=>$_POST['skjult_email']);

				if($formdata['action']=='netsignup') {
					backend_tilmeld_bruger_til_net($brugernavn);
					$formdata_databasehent = true;
					$messages[] = 'The user is now signed up for the internet.';
				}
				if($formdata['action']=='netsignoff') {
					backend_frameld_bruger_fra_net($brugernavn);
					$formdata_databasehent = true;
					$messages[] = 'The user is now signed off.';
				}

				// hvis netsignup eller resetpassword er valgt, saa generer ogsaa nyt password
				if($formdata['action']=='netsignup' || $formdata['action']=='resetpassword') {
					$setbrugerdata['password'] = backend_generer_passwd();
					$smarty->assign('printlogin', true);
					$smarty->assign('password', $setbrugerdata['password']);
					$messages[] = 'A new login paper is created. <a href="javascript:window.print();">Print this page</a>.';
					$formdata_databasehent = true;
					
				}
				if($formdata['action']=='nothing')
					$messages[] = 'User data updated.';

				// opdater brugerdata
				backend_set_brugerdata($brugernavn, $setbrugerdata);
				backend_opdater_datafiler();
			}	
		}
		else { // hvis data ikke er sendt, brug data fra database
			$formdata_databasehent = true;
		}
	
		$brugere = backend_hent_brugere(array('brugernavn'=>$brugernavn));
		$brugerdata = $brugere[0];
		if($formdata_databasehent==true)
			$formdata = array('email'=>$brugerdata['email'], 'mobilnummer'=>$brugerdata['mobilnummer'], 'hjemmeside'=>$brugerdata['hjemmeside'], 'skjult_navn'=>(bool)$brugerdata['skjult_navn'], 'skjult_email'=>(bool)$brugerdata['skjult_email']);
	
		$smarty->assign('brugerdata', $brugerdata);
		$smarty->assign('formdata', $formdata);
		$smarty->assign('messages', $messages);
		$smarty->assign('contenttemplate', 'admin-users-edit.tpl');
	}
}
catch(Exception $e) {
	fatal_error($e);	
}

$smarty->display(WEBFRONTEND_MAINTEMPLATE); 
