#!/usr/bin/php
<?php
require(dirname(__FILE__).'/../backend/backend.inc.php');
require(dirname(__FILE__).'/faelles.inc.php');

/**
 * Opret mappe hvis den ikke findes i forvejen ('ne': not exists).
 * 
 * @param string $path
 */
function mkdir_ne($path) {
	if(!file_exists($path))
		mkdir($path, 0755, true);
}

/**
 * Lav options-strengen til list-filerne for et givet medlem.
 * 
 * @param string $path
 */
function lav_options($medlem) {
	$s = '';
	if(array_key_exists('mail_modtag', $medlem) && $medlem['mail_modtag'] == 0)
		$s .= '!';
	if($medlem['mail_forfatter']) $s .= '+';
	if($s) $s = '>' . $s;
	return $s;
}

/*
 *******************************************************************************
 */

kun_root();
vent_indtil_eneste_instans();
vent_indtil_ingen_instans_af('/usr/bin/minimalist');

// opret hoved minimalist-mappen hvis den ikke findes i forvejen
mkdir_ne(MINIMALIST_DIR);

$aliases_linjer = explode("\n", trim(file_get_contents(UNIXFILE_ALIASES)));

$mail_liste_navne = array();
$grupper = backend_hent_grupper();
foreach($grupper as $gruppe) {
	$liste_navn = preg_replace('/[^A-Za-z._-]/', '',
		$gruppe['mail_liste_navn']);

	if(!$liste_navn) continue;

	$mail_liste_navne[] = $liste_navn;

	// lav mappe til mailliste navne, og lav fil med mail addresser
	mkdir_ne(MINIMALIST_DIR . '/' . $liste_navn);
	$liste = '';
	$medlemmer = array_merge(
		backend_hent_gruppe_medlemmer($gruppe['gruppenavn']),
		backend_hent_gruppe_medlemmer_eksterne($gruppe['gruppenavn']));
	
	if($gruppe['gruppenavn'] == 'nyk_alle_beboere') file_put_contents('/tmp/mailtest', print_r(backend_hent_gruppe_medlemmer('nyk_alle_beboere'), true));

	foreach($medlemmer as $medlem) {
		if(!$medlem['email']) continue;
		$liste .= $medlem['email'] . lav_options($medlem) . "\n";
	}
	$liste_sti = MINIMALIST_DIR . '/' . $liste_navn . '/list';
	if(@file_get_contents($liste_sti) !== $liste)
		file_put_contents($liste_sti, $liste, LOCK_EX);
	
	// skriv footer-fil hvis der er angivet footer
	$footer_sti = MINIMALIST_DIR . '/' . $liste_navn . '/footer';
	if($gruppe['mail_footer']) {
		$footer = wordwrap(iconv('UTF-8', 'ISO-8859-1', $gruppe['mail_footer']), 78);
		if(@file_get_contents($footer_sti) !== $footer)
			file_put_contents($footer_sti, $footer, LOCK_EX);
	} else {
		@unlink($footer_sti);
	}
	
	// skriv config-fil for listen
	$config_sti = MINIMALIST_DIR . '/' . $liste_navn . '/config';
	$config = '';
	if($gruppe['mail_skriverettigheder'] == 'alle')
		$config = "security = none\nstatus = closed\n";
	if($gruppe['mail_skriverettigheder'] == 'alle_medlemmer')
		$config = "security = careful\nstatus = closed\n";
	if($gruppe['mail_skriverettigheder'] == 'udvalgte_medlemmer')
		$config = "security = careful\nstatus = closed,ro\n";
	if(@file_get_contents($config_sti) !== $config)
		file_put_contents($config_sti, $config, LOCK_EX);

	// tjek om listen står i aliases
	$korrekt_linje =
		$liste_navn . ': "|/usr/bin/minimalist ' . $liste_navn . '"';
	$fundet = false;
	for($i = 0; $i < count($aliases_linjer); $i++) {
		if(stripos(trim($aliases_linjer[$i]), $liste_navn . ':') === 0) {
			// slet linje hvis allerede fundet
			if($fundet) array_splice($aliases_linjer, $i, 1);

			// sikrer at linje er en korret mailliste linje
			if( trim($aliases_linjer[$i]) != $korrekt_linje)
				$aliases_linjer[$i] = $korrekt_linje;

			$fundet = true;
		}
	}
	if(!$fundet) $aliases_linjer[] = $korrekt_linje;
}

// skriv aliases filen tilbage
$aliases = implode("\n", $aliases_linjer) . "\n";
if(file_get_contents(UNIXFILE_ALIASES) !== $aliases) 
	file_put_contents(UNIXFILE_ALIASES, $aliases, LOCK_EX);

// slet overskydende mapper
$dh = opendir(MINIMALIST_DIR);
while(($f = readdir($dh)) !== false) {
	if(is_dir(MINIMALIST_DIR . '/' . $f) && $f != '.' && $f != '..'
		&& !in_array($f, $mail_liste_navne)) {
		$dir = MINIMALIST_DIR . '/' . $f;
		@unlink($dir . '/list');
		@unlink($dir . '/config');
		@unlink($dir . '/footer');
		@unlink($dir . '/info');
		@rmdir(MINIMALIST_DIR . '/' . $f);
	}
}

// skriv list.lst filen
$listslst = implode("\n", $mail_liste_navne) . "\n";
$listslst_dir = MINIMALIST_DIR . '/lists.lst';
if(file_get_contents($listslst_dir) !== $aliases)
	file_put_contents($listslst_dir, $listslst, LOCK_EX);

// opdater aliases db
shell_exec('newaliases');
