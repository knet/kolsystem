<?php
try {
	require_once('phpincludes/init.inc.php');
	auth_require_login();

	// hent medlemskaber
	$grupper = backend_hent_brugers_medlemskaber($_SESSION['brugernavn']);

	// hvis data er sendt
	if($_POST['action']=='save') {
		// opdater mail_modtag for hver af grupperne
		$modtag_mail_grupper = $_POST['modtag_mail'];
		foreach($grupper as $gruppe) {
			$ny_mail_modtag = ($modtag_mail_grupper[$gruppe['gruppenavn']]==1)||($gruppe['mail_obligatorisk']==1) ? true : false;
			if($gruppe['mail_modtag']!=$ny_mail_modtag) {
				$opdateret = true;
				backend_set_gruppemedlemskab($_SESSION['brugernavn'], $gruppe['gruppenavn'], array('mail_modtag' => $ny_mail_modtag));				
			}
		}

		// hvis der er opdateret i et gruppemedlemskab
		if($opdateret) {
			backend_opdater_datafiler();
			$smarty->assign('messages', array('Your groups are updated.'));			
		}
	
		// hent de opdaterede medlemskaber
		$grupper = backend_hent_brugers_medlemskaber($_SESSION['brugernavn']);
	}

	$smarty->assign('grupper', $grupper);
	$smarty->assign('contenttemplate', 'user-groupmemberships.tpl');
}
catch(Exception $e) {
	fatal_error($e);	
}


$smarty->display(WEBFRONTEND_MAINTEMPLATE);