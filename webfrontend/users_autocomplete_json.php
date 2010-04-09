<?php
	require('../backend/backend.inc.php');
	$users = array();
    $hent_brugere = backend_hent_brugere(array(), 'vaerelse', false, 'alle');
	foreach($hent_brugere as $bruger) 
		$users[] = array('brugernavn'=>$bruger['brugernavn'], 'navn' =>$bruger['navn'], 'vaerelse'=>$bruger['vaerelse']);
	echo 'var users = '.json_encode($users).';';
?>

