#!/usr/bin/php
<?php
require(dirname(__FILE__).'/../backend/backend.inc.php');



/**
 * Gem log-linje. Hvis givne besked er flere linjer, konverter da linjebrud til
 * komma.
 * 
 * @param string $s Log-linje.
 */
function log_skriv($s) {
	$s = str_replace("\r\n", ', ', $s);
	$s = str_replace("\n", ', ', $s);
	$s = str_replace("\r", ', ', $s);
	$s = date('D, d M Y H:i:s') . ' ' . $s. "\n";
	file_put_contents(BRUGEROPRYDNINGBATCH_LOGFILE, $s, FILE_APPEND);
}

/*
 *******************************************************************************
 */

log_skriv('starter');

// frameld brugere hvis udflytningsdato er passeret
$udflyttede_brugere = backend_hent_brugere(array('vaerelse_type' => 'begge'),
	'vaerelse', false, 'udflyttede');
foreach($udflyttede_brugere as $bruger) {
	if(strtotime($bruger['udflytning']) < (time()-40*86400)) {
		$brugerdata = json_encode($bruger);
		$grupper = json_encode(backend_dbquery(
			'SELECT * FROM gruppemedlemskaber where brugernavn=?',
			array($bruger['brugernavn'])));
		log_skriv("Sletter bruger: $brugerdata. Gruppetilmeldinger: $grupper");
		backend_slet_bruger($bruger['brugernavn']);
	}
}

// TODO slet fremlejere som har kab id som ikke matcher en alm. lejers kab id

?>
