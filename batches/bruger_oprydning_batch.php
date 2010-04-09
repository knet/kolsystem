#!/usr/bin/php
<?php
require(dirname(__FILE__).'/../backend/backend.inc.php');

define('BRUGEROPRYDNINGBATCH_LOGFILE', '../logs/bruger_oprydning_batch.log');

/* FUNKTIONER */

function writelog($str) {
	$s = date('D, d M Y H:i:s').' '.$str."\n";
	echo $str."\n";
	file_put_contents(BRUGEROPRYDNINGBATCH_LOGFILE, $s, FILE_APPEND | LOCK_EX);
}

/* PROGRAMMET STARTER HER */

// frameld brugere hvis udflytningsdato er passeret
$udflyttede_brugere = backend_dbquery("SELECT brugernavn FROM brugere WHERE CURDATE()>udflytning");
foreach($udflyttede_brugere as $bruger) {
	try {
		backend_slet_bruger($bruger['brugernavn']);
		writelog('Bruger: '.$bruger['brugernavn'].' tilføjet i tidligere_beboere, slettet i brugere');
	} catch(backend_InsertTidligereBeboerFejlException $e) {
		writelog('FEJL: Kunne ikke tilføje kopi af brugeren '.$bruger['brugernavn'].' til tidligere_beboere.');
		echo $e;
	} catch(backend_DeleteBrugerFejlException $e) {
		writelog('FEJL: Kunne ikke slette brugeren '.$bruger['brugernavn']);
		echo $e;
	}	
}





// TODO slet fremlejere som har kab id som ikke matcher en alm. lejers kab id



?>
