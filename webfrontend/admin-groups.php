<?php
try {
	require_once('phpincludes/init.inc.php');
	$messages = array();
	$errors = array();
	auth_require_login();
	$gruppenavn = $_REQUEST['groupname'];

	// hent grupper, som bruger maa administrere
	if (auth_is_member_of('nyk_netdrift')) {
		// hvis medlem af nyk_netdrift, maa bruger administrere alle grupper
		$grupper = backend_hent_grupper();	
	}
	else {
		// hvis ikke medlem af nyk_netdrift, maa bruger administrere alle grupper som bruger er administrator af
		$grupper = backend_hent_brugers_admin_medlemskaber($_SESSION['brugernavn']);
		if(sizeof($grupper)==0)
			die('Access denied.');
		if (!empty($gruppenavn)) {
			// kontroller at bruger har administratorrettighed til gruppen
			$admin_gruppemedlemskab = backend_hent_gruppemedlemskab($_SESSION['brugernavn'], $gruppenavn);
			if($admin_gruppemedlemskab['gruppeadmin']!=true)
				die('Access denied');
		}

	}

	if(empty($gruppenavn)) {
		// vis gruppeoversigt
		$smarty->assign('grupper', $grupper);
		$smarty->assign('contenttemplate', 'admin-groups-list.tpl');	
	}
	else {
		// vis administration af gruppens medlemmer
		$gruppe = backend_hent_gruppe($gruppenavn);
		$gruppemedlemmer = backend_hent_gruppe_medlemmer($gruppenavn);
		$gruppemedlemmer_eksterne = backend_hent_gruppe_medlemmer_eksterne($gruppenavn);
		
		if($_POST['action']=='save') {
			$mail_modtag_medlemmer = $_POST['mail_modtag'];
			$mail_forfatter_medlemmer = $_POST['mail_forfatter'];
			// for hvert gruppemedlem
			foreach($gruppemedlemmer as $medlem) {
				// tjek for rettelser i mail_modtag og mail_forfatter
				$delta = array();
				$ny_mail_modtag = ($mail_modtag_medlemmer[$medlem['brugernavn']]==1);
				if($medlem['mail_modtag']!=$ny_mail_modtag)
					$delta['mail_modtag'] = $ny_mail_modtag;
				$ny_mail_forfatter = ($mail_forfatter_medlemmer[$medlem['brugernavn']]==1);
				if($medlem['mail_forfatter']!=$ny_mail_forfatter)
					$delta['mail_forfatter'] = $ny_mail_forfatter;
				
				// hvis der er rettelser, så sæt gruppemedlemskabet
				if(sizeof($delta)>0) {
					$opdateret = true;
					backend_set_gruppemedlemskab($medlem['brugernavn'], $gruppenavn, $delta);
				}
			}
			
			// hvis der blev opdateret i medlemskaber
			if($opdateret)
				$messages[] = 'Group members updated.';
			
			if(!empty($_POST['addmember'])) {
				$opdateret = true;
				try {
					backend_set_gruppemedlemskab($_POST['addmember'], $gruppenavn);					
					$messages[] = 'Membership for user <i>'.$_POST['addmember'].'</i> added.';
				}
				catch(forkert_input_exception $e) {
					$errors[] = 'Membership for user <i>'.$_POST['addmember'].'</i> could not be added.';
				}
				
			}
			if(!empty($_POST['removemember'])) {
				$opdateret = true;
				backend_slet_gruppemedlemskab($_POST['removemember'], $gruppenavn);
				$messages[] = 'Membership for user <i>'.$_POST['removemember'].'</i> removed.';
				
			}

			// hvis noget som helst er opdateret
			if($opdateret) {
				backend_opdater_datafiler();				
				// hent de opdaterede gruppemedlmemmer
				$gruppemedlemmer = backend_hent_gruppe_medlemmer($gruppenavn);
			}
			
		}

		$smarty->assign('gruppe', $gruppe);
		$smarty->assign('gruppemedlemmer', $gruppemedlemmer);
		$smarty->assign('gruppemedlemmer_eksterne', $gruppemedlemmer_eksterne);
		$smarty->assign('messages', $messages);			
		$smarty->assign('errors', $errors);			
		$smarty->assign('include_autocomplete', true);
		$smarty->assign('contenttemplate', 'admin-groups-members.tpl');	
	}

}
catch(Exception $e) {
	fatal_error($e);	
}

$smarty->display(WEBFRONTEND_MAINTEMPLATE); 
