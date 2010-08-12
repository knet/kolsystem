#!/usr/bin/php
<?php
require(dirname(__FILE__).'/../backend/backend.inc.php');
require(dirname(__FILE__).'/faelles.inc.php');

function hent_kolon_separeret_fil($filename) {
	$file = explode("\n", @file_get_contents($filename));
	$res = array();
	foreach($file as $line) {
		$data = explode(':', $line);
		$res[$data[0]] = $data;
	}
	return $res;
}

function gem_kolon_separeret_fil($filename, $arr) {
	$s = '';
	foreach($arr as $l) $s .= implode(':', $l) . "\n";

	if(@file_get_contents($filename) !== $s)
		file_put_contents($filename, $s, LOCK_EX)
			or exit('Kan ikke skrive til filen '.$filename."\n");
}

function trimlower_array_keys($arr) {
	if (!is_array($arr)) return(strtolower(trim($arr)));
	$res = array();
	foreach($arr as $k=>$v) {
		$k = strtolower(trim($k));
		if (!empty($k)) $res[$k] = $v;
	}
	return $res;
}

/*
 *******************************************************************************
 */

//kun_root();
vent_indtil_eneste_instans();

// indlæs passwd fil 
$unix_brugere = trimlower_array_keys(hent_kolon_separeret_fil(UNIXFILE_PASSWD));

// indlæs shadow fil
$unix_passwords = trimlower_array_keys(hent_kolon_separeret_fil(UNIXFILE_SHADOW));

// indlæs smbpasswd fil
$smbpasswd = trimlower_array_keys(hent_kolon_separeret_fil(SMBPASSWD));

// hvis root ikke eksisterer i unix brugere og unix passwords afbrydes, da noget så er helt galt
if(!isset($unix_brugere['root']) || !isset($unix_passwords['root']))
	exit('AFBRUDT: root bruger kunne ikke findes og en fejl ved indlæsning er derfor tilsyneladende opstået!'."\n");

// find næste user id under 40000
$next_uid = 0;
foreach($unix_brugere as $bruger) {
	if($bruger[2]>$next_uid && $bruger[2]<40000) $next_uid = $bruger[2]+1;
}

// hent brugere fra database og tilret nykbrugere, passwords og samba users efter denne
$knetshadowfile_content = '';
$fundne_brugernavne = array();
$db_brugere = backend_hent_brugere();
$db_brugere_fremtidige = backend_hent_brugere($data = array(), $sorterEfter = "vaerelse",
	$asc = false, $status = 'fremtidige');
$db_brugere = array_merge($db_brugere, $db_brugere_fremtidige);
foreach($db_brugere as $bruger) {
	// klargør brugernavn og kontroller at det starter med 'nyk'
	$brugernavn = $bruger['brugernavn'];
	$lookup_brugernavn = strtolower(trim($brugernavn));
	if(substr($lookup_brugernavn, 0, 3) != 'nyk') continue;

	// hvis brugernavnet findes i unix brugere så tilret, ellers tilføj
	if(isset($unix_brugere[$lookup_brugernavn])) {
		$unix_brugere[$lookup_brugernavn][4] = iconv('UTF-8', 'ISO-8859-1', $bruger['navn']);
		$unix_passwords[$lookup_brugernavn][1] = $bruger['password_unix'];
	}
	else {
		$unix_brugere[$lookup_brugernavn] = array($brugernavn, 'x', $next_uid++, DEFAULT_GID, $bruger['navn'], '/nonexistent', '/nyksystem/clitools/dummyshell');
		$unix_passwords[$lookup_brugernavn] = array($brugernavn, $bruger['password_unix'], '14590', '0', '99999', '7', '', '', '');
	}

	// tilføj bruger i knet shadowfil, såfremt bruger ikke er spærret (overskriv tidligere brugere)
	if($bruger['net_tilmeldt_dato'] && $bruger['spaerret_net_konto']!=1)
		$knetshadowfile_content .= $brugernavn.':'.$unix_passwords[$lookup_brugernavn][1]."\n";

	// hvis brugernavnet findes i smbpasswd så tilret, ellers tilføj
	if(isset($smbpasswd[$lookup_brugernavn]))
		$smbpasswd[$lookup_brugernavn][3] = $bruger['password_nt'];
	else
		$smbpasswd[$lookup_brugernavn] = array(
			$brugernavn, $unix_brugere[$lookup_brugernavn][2],
			'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', $bruger['password_nt'],
			'[U          ]', 'LCT-4B3BF4BD', '');

	// gem dette brugernavn i array over fundne brugere
	$fundne_brugernavne[$lookup_brugernavn] = true;
}

// fjern overskydende brugere fra unix_brugere (maskin-konti slutter på $)
foreach($unix_brugere as $brugernavn => $v) {
	$b = strtolower(trim($brugernavn));
	if(substr($b, 0, 3) == 'nyk' && !isset($fundne_brugernavne[$b])
		&& $b[strlen($b)-1] != '$') {
		unset($unix_brugere[$b]);
	}
}

// fjern overskydende brugere fra shadow  (maskin-konti slutter på $)
foreach($unix_passwords as $brugernavn => $v) {
	$b = strtolower(trim($brugernavn));
	if(substr($b, 0, 3) == 'nyk' && !isset($fundne_brugernavne[$b])
		&& $b[strlen($b)-1] != '$') {
		unset($unix_passwords[$b]);
	}
}

// fjern overskydende brugere fra smbpasswd (maskin-konti slutter på $)
foreach($smbpasswd as $brugernavn => $v) {
	$b = strtolower(trim($brugernavn));
	if(substr($b, 0, 3) == 'nyk' && !isset($fundne_brugernavne[$b])
		&& $b[strlen($b)-1] != '$') {
		unset($smbpasswd[$b]);
	}
}

// skriv ny passwd fil
gem_kolon_separeret_fil(UNIXFILE_PASSWD, $unix_brugere);
	
// skriv ny shadowfil
gem_kolon_separeret_fil(UNIXFILE_SHADOW, $unix_passwords);

// skriv ny knet shadow-lignende fil
// UDKOMMENTERET INDTIL VI RSYNCER TIL KNET
file_put_contents(RSYNC_DIR.'nyk_ekstern', $knetshadowfile_content);

// skriv ny smbpasswd fil
gem_kolon_separeret_fil(SMBPASSWD, $smbpasswd);


// indlæs group fil
$unix_grupper = trimlower_array_keys(hent_kolon_separeret_fil(UNIXFILE_GROUP));

// find næste group id
$next_gid = 0;
foreach($unix_grupper as $gruppe) {
	if($gruppe[2]>$next_gid && $gruppe[2]<40000) $next_gid = $gruppe[2]+1;
}

// hent grupper fra database, og opdater unix grupper
$db_grupper = backend_hent_grupper();
foreach($db_grupper as $gruppe) {
	$db_gruppemedlemmer = backend_hent_gruppe_medlemmer($gruppe['gruppenavn']);
	$lookup_gruppenavn = strtolower(trim($gruppe['gruppenavn']));
	
	$medlemmer_brugernavne = array();
	foreach($db_gruppemedlemmer as $m)
		$medlemmer_brugernavne[] = $m['brugernavn'];
	
	
	if(isset($unix_grupper[$lookup_gruppenavn])) { 
		$unix_gruppemedlemmer = explode(',', $unix_grupper[$lookup_gruppenavn][3]);

		// tilføj de nykbrugere der er blevet medlem 
		foreach($medlemmer_brugernavne as $dbmedlem) {
			if(!in_array($dbmedlem, $unix_gruppemedlemmer)) {
				$unix_gruppemedlemmer[] = $dbmedlem;
			}
		}
		
		// fjern de nykbrugere der ikke længere er medlem
		$unix_gruppemedlemmer_fjern = array();
		foreach($unix_gruppemedlemmer as $k=>$medlem) {
			if(substr($medlem, 0, 3) == 'nyk' && !in_array($medlem, $medlemmer_brugernavne)) {
				$unix_gruppemedlemmer_fjern[] = $medlem;
			}
		}
		$unix_gruppemedlemmer = array_diff($unix_gruppemedlemmer, $unix_gruppemedlemmer_fjern);

		$unix_grupper[$lookup_gruppenavn][3] = implode(',', $unix_gruppemedlemmer);
	}
	else {
		$unix_grupper[$lookup_gruppenavn] = array(0 => $gruppe['gruppenavn'], 1 => 'x', 2 => $next_gid++, 3 => implode(',', $medlemmer_brugernavne));
	}
}

// skriv ny group fil
gem_kolon_separeret_fil(UNIXFILE_GROUP, $unix_grupper);

// rsync med knet
if(file_exists(RSYNC_DIR.'nyk_ekstern')) {
	exec('/usr/bin/rsync --verbose --timeout=30 -e "/usr/bin/ssh -i '.RSYNC_PRIVKEY.'" -acW '.RSYNC_DIR.' rsync-nyk@'.RSYNC_HOST.':/home/rsync-nyk');	
}

