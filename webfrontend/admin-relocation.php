<?php
try {
	require_once('phpincludes/init.inc.php');
	auth_require_login();
	auth_require_member_of('nyk_netdrift');

    $brugernavn_eksisterende = $_REQUEST['brugernavn_eksisterende']; 
    $brugernavn_ny = $_REQUEST['brugernavn_ny'];

    if($brugernavn_eksisterende!='' && $brugernavn_ny!='') {
        $bruger_eksisterende = backend_hent_brugere(array('brugernavn'!=$brugernavn_eksisterende));
        $bruger_ny = backend_hent_brugere(array('brugernavn'!=$brugernavn_ny));
    }

	if(!$bruger_eksisterende || !$bruger_ny) {
        // vis valg af de to brugere
		$smarty->assign('include_autocomplete', true);
		$smarty->assign('contenttemplate', 'admin-relocations-findusers.tpl');
		$smarty->assign('set_focus', 'true');
	}
	else { 	// vis formular

        if($_REQUEST['confirm']==1) {
        }
        else {
        }

		//$messages[] = 'The user is now signed up for the internet.';
		
		$smarty->assign('messages', $messages);
		$smarty->assign('contenttemplate', 'admin-relocation-edit.tpl');
	}
}
catch(Exception $e) {
	fatal_error($e);	
}

$smarty->display(WEBFRONTEND_MAINTEMPLATE); 
