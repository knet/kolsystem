#!/usr/bin/php
<?php
require(dirname(__FILE__).'/../backend/backend.inc.php');

if (definded(KABAS_ENABLE) AND constant(KABAS_ENABLE) == false) {
	exit();
}

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
	file_put_contents(KABASIMPORT_LOGFILE, $s, FILE_APPEND);
}

/**
 * Gem log-linje og afslut.
 * 
 * @param string $s Log-linje.
 */
function fatal_fejl($s) {
	log_skriv('fatal fejl: ' . $s);
	exit();
}

/**
 * Konverter navn til rigtig tegnsæt og slet evt. uønskede tegn og ord.
 * 
 * @param string $navn
 * @return string
 */
function klargoer_navn($navn) {
	$navn = iconv("ISO-8859-1", "UTF-8", $navn);
	$navn = str_replace(" og", "", $navn);
	$navn = str_replace("&amp;", "", $navn);
	$navn = preg_replace('/[^\wæøåÆØÅöÖüÜéÉíÍäá \'.-]/', "", $navn);
	$navn = trim($navn);
	return $navn;
}

/**
 * Konverter dato til databasens format. Forvent at input ikke er i tvetydigt
 * format.
 * 
 * @param string $dato
 * @return string
 */
function fixdate($date) {
	$timestamp = strtotime($date);
	if($timestamp === false) return null;
	return date('Y-m-d', $timestamp);
}

/**
 * Konverter dato til databasens format. Forvent at dato er i formattet
 * 'DD.MM.YYYY', ellers 'YYYY-MM-DD'.
 * 
 * @param string $dato
 * @return string
 */
function fixfremldate($fremlejerdate) {
	if(strpos($fremlejerdate, '-') !== false)
		return fixdate($fremlejerdate);

	list($d, $m, $Y) = explode('.', $fremlejerdate);
	return fixdate($Y.'-'.$m.'-'.$d);
}

/**
 * Tjek at givne KABAS udtræk er i korrekt format, og parse udtrækket med
 * SimpleXML.
 * 
 * @param string $xmlstring
 * @return simplexml-objekt
 */
function parse_kabas_udtraek($xmlstring) {
	// tjek at data er valid imod et XML schema
	$xsd = file_get_contents(KABAS_UDTRAEK_SCHEMA);
	set_error_handler('parse_kabas_udtraek_error');
	$dom = new DOMDocument;
	$dom->loadXML($xmlstring);
	$valid = $dom->schemaValidateSource($xsd);
	restore_error_handler();

	// parse data med SimpleXML
	return simplexml_load_string($xmlstring);
}

/**
 * Hjælpe-funktion til parse_kabas_udtraek.
 * 
 * @param string $xmlstring
 * @return simplexml-objekt
 */
function parse_kabas_udtraek_error($errno, $errstr, $errfile, $errline) {
	fatal_fejl($errstr);
}

/**
 * @return Liste med alle KAB lejemål ID'er på brugerne i databasen.
 */
function hent_kab_lejemaal_ids() {
	$r = backend_dbquery("SELECT kab_lejemaal_id FROM brugere");
	$kab_lejemaal_ids = array();
	foreach($r as $row) {
		if($row['kab_lejemaal_id']) {
			$kab_lejemaal_ids[] = $row['kab_lejemaal_id'];
		}
	}
	return $kab_lejemaal_ids;
}

/**
 * @return Liste med alle værelsesnavne fra databasen.
 */
function hent_vaerelse_navne() {
	$r = backend_dbquery("SELECT vaerelse FROM vaerelser");
	$vaerelser = array();
	foreach($r as $row) {
		$vaerelser[] = $row['vaerelse'];
	}
	return $vaerelser;
}

/**
 * @param string $kab_id KAB lejemål ID
 * @return String med værelsesnavn (f.eks. C31)
 */
function vaerelse_fra_kab_id($kab_id) {
	$s = substr($kab_id, 0, 2);
	$a = array('01' => 'A', '02' => 'B', '03' => 'C', '04' => 'D',
		'05' => 'E', '06' => 'F', '07' => 'G', '08' => 'H',
		'09' => 'J', '10' => 'K', '11' => 'L', '12' => 'M',
		'13' => 'N', '14' => 'O', '15' => 'P', '16' => 'R',
		'17' => 'S', '18' => 'T');
	if(!isset($a[$s])) return;
	$vaerelse = $a[$s];
	$vaerelse .= substr($kab_id, 2, 2);
	return $vaerelse;
}

/**
 * Opretter brugere ud fra 'lejemaal'-entry fra KABAS udtræk. Hvis det er en 
 * lejlighed oprettes begge brugere. Fremlejere oprettes ikke.
 *
 * @param object $lejemaal SimpleXML-objekt af 'lejemaal'-entry
 */
function opret_brugere_fra_kabas_lejemaal($lejemaal) {
	// sæt datoer til null hvis ikke givet
	$ind = $lejemaal->indDato ? fixdate($lejemaal->indDato) : null;
	$ud = $lejemaal->udDato ? fixdate($lejemaal->udDato) : null;

	$id = $lejemaal->id;
	$v = vaerelse_fra_kab_id($lejemaal->id);
	$n = klargoer_navn($lejemaal->lejer1);
	try {
		$brugernavn = backend_ny_bruger($v, $n,	$ind, $ud, $id, 'lejer1');
		log_skriv('oprettet bruger: ' . $brugernavn . ', navn: ' . $n
			. ', KAB lejemaal id: ' . $id);
	} catch(Exception $e) {
		log_skriv('fejl ved oprettelse af bruger. Navn: ' . $n .
			', KAB lejemaal id: ' . $id);
	}

	// er der en lejer2 og er dette en lejlighed?
	if(isset($lejemaal->lejer2) && substr($lejemaal->id, 2, 2) < 15) {
		$n = klargoer_navn($lejemaal->lejer2);
		try {
			$brugernavn = backend_ny_bruger($v, $n,	$ind, $ud, $id, 'lejer2');
			log_skriv('oprettet bruger: ' . $brugernavn . ', navn: ' . $n
				. ', KAB lejemaal id: ' . $id);
		} catch(Exception $e) {
			log_skriv('fejl ved oprettelse af bruger. Navn: ' . $n .
				', KAB lejemaal id: ' . $id);
		}
	}
}

/**
 * Opretter fremlejer bruger ud fra 'lejemaal'-entry fra KABAS udtræk.
 *
 * @param object $lejemaal SimpleXML-objekt af 'lejemaal'-entry
 */
function opret_fremlejer_bruger_fra_kabas_lejemaal($lejemaal) {
	// sæt datoer til null hvis ikke givet
	$ind = $lejemaal->fremlFra ? fixfremldate($lejemaal->fremlFra) : null;
	$ud = $lejemaal->fremlTil ? fixfremldate($lejemaal->fremlTil) : null;

	$id = $lejemaal->id;
	$v = vaerelse_fra_kab_id($lejemaal->id);
	$n = klargoer_navn($lejemaal->fremlNavn);

	try {
		$brugernavn = backend_ny_bruger($v, $n,	$ind, $ud, $id, 'fremlejer');
		log_skriv('oprettet bruger: ' . $brugernavn . ', navn: ' . $n
			. ' (fremlejer), KAB lejemaal id: ' . $id);
	} catch(Exception $e) {
		log_skriv('fejl ved oprettelse af bruger. Navn: ' . $n .
			', KAB lejemaal id: ' . $id);
	}
}

/**
 * Opdaterer eventuelle felter i bruger-tabellen i databasen ud fra
 * 'lejemaal'-entry fra KABAS udtræk. Fremlejerdata opdateres ikke.
 *
 * @param object $lejemaal SimpleXML-objekt af 'lejemaal'-entry
 */
function opdater_brugere_fra_kabas_lejemaal($lejemaal, $lejer_type) {
	if($lejer_type != 'lejer1' && $lejer_type != 'lejer2') return;

	// hent bruger med dette lejemaal id
	$r = backend_hent_brugere(array('kab_lejemaal_id' => $lejemaal->id,
				'lejer_type' => $lejer_type));
	if(count($r) > 0) {
		$bruger = $r[0];
		
		// ting der skal ændres
		$delta = array();

		// er indflytningsdato blevet ændret?
		$ind = $lejemaal->indDato ? fixdate($lejemaal->indDato) : null;
		if($bruger['indflytning'] != $ind) 
			$delta['indflytning'] = fixdate($lejemaal->indDato);

		// er udflytningsdato blevet ændret?
		$ud = $lejemaal->udDato ? fixdate($lejemaal->udDato) : null;
		if($bruger['udflytning'] != $ud)
			$delta['udflytning'] = fixdate($lejemaal->udDato);

		// hvis der er ændringer skal de gemmes i databasen
		if(count($delta) != 0) {
			backend_set_brugerdata($bruger['brugernavn'], $delta);
			log_skriv('rettet brugerdata for '.$bruger['brugernavn']
				. ', KAB lejemaal id: ' . $lejemaal->id
				. ', delta: ' . json_encode($delta));
		}
	}
}

/**
 * Opdaterer eventuelle felter for fremlejere i bruger-tabellen i databasen ud
 * fra 'lejemaal'-entry fra KABAS udtræk.
 *
 * @param object $lejemaal SimpleXML-objekt af 'lejemaal'-entry
 */
function opdater_fremlejer_bruger_fra_kabas_lejemaal($lejemaal, $brugernavn) {
	$r = backend_hent_brugere(array('brugernavn' => $brugernavn), 'vaerelse', false, 'alle');
	if(count($r) == 0) {
		log_skriv(
			'fejl, forsoegte at opdatere fremlejer som ikke var oprettet, '
			. 'KAB lejemaal id: ' . $lejemaal->id
			. ', brugernavn: ' . $brugernavn);
	} else {
		$bruger = $r[0];

		// ting der skal ændres
		$delta = array();

		// er fremlejer navn blevet ændret?
		$navn = klargoer_navn($lejemaal->fremlNavn);
		if($bruger['navn'] != $navn)
			$delta['navn'] = $navn;

		// er indflytningsdato blevet ændret?
		$ind = $lejemaal->fremlFra ? fixfremldate($lejemaal->fremlFra) : null;
		if($bruger['indflytning'] != $ind)
			$delta['indflytning'] = $ind;

		// er udflytningsdato blevet ændret?
		$ud = $lejemaal->fremlTil ? fixfremldate($lejemaal->fremlTil) : null;
		if($bruger['udflytning'] != $ud)
			$delta['udflytning'] = $ud;

		// hvis der er ændringer skal de gemmes i databasen
		if($delta != null) {
			backend_set_brugerdata($bruger['brugernavn'], $delta);
			log_skriv(
				'rettet brugerdata for '.$bruger['brugernavn'] . '. Navn: '
				. $navn . ' (fremlejer), KAB lejemaal id: ' . $lejemaal->id
				. ', delta: ' . json_encode($delta));
		}
	}
}

/*
 *******************************************************************************
 */

log_skriv('starter');

// hent nyeste udtræk ned
$xmlstring = file_get_contents(KABAS_DATAFILE_PATH)
	or fatal_fejl();
$xml = parse_kabas_udtraek($xmlstring);
file_put_contents(KABAS_DATAFILE_NEWEST, $xmlstring);

// hent nogle data fra nuværende database
$kab_lejemaal_ids = hent_kab_lejemaal_ids();
$vaerelser = hent_vaerelse_navne();

// itererer igennem hvert lejemål fra udtraek
foreach($xml->lejemaal as $lejemaal) {
	// tjek om dette er en håbløst gammel beboer, derfor med fejlbehæftet data
	if(strtotime($lejemaal->indDato) < strtotime('2002-01-01')) continue;

	// parse KAB ID og tjek om dette værelse findes
	$vaerelse = vaerelse_fra_kab_id($lejemaal->id);
	if(!in_array($vaerelse, $vaerelser)) continue;

	// opdater hvis findes i forvejen, ellers opret
	if(in_array($lejemaal->id, $kab_lejemaal_ids)) {
		opdater_brugere_fra_kabas_lejemaal($lejemaal, 'lejer1');
		opdater_brugere_fra_kabas_lejemaal($lejemaal, 'lejer2');
	} else {
		// skip hvis gammel beboer
		if(isset($lejemaal->udDato) && time() > strtotime($lejemaal->udDato))
			continue;

		opret_brugere_fra_kabas_lejemaal($lejemaal);		
	}

	// hvis der er specificeret en fremlejer og dennes udløbsdato
	if(isset($lejemaal->fremlNavn) && isset($lejemaal->fremlFra)) {
		$navn = klargoer_navn($lejemaal->fremlNavn);
		$ind = fixfremldate($lejemaal->fremlFra);
		$ud = fixfremldate($lejemaal->fremlTil);
		$fremlejer_allerede_oprettet = false;

		// tjek om der findes fremlejer på dette kab_lejemaal_id i forvejen
		$fremlejere = backend_hent_brugere(array(
			'kab_lejemaal_id' => $lejemaal->id,	'lejer_type' => 'fremlejer'));
		$fremlejere = array_merge($fremlejere, backend_hent_brugere(array(
			'kab_lejemaal_id' => $lejemaal->id, 'lejer_type' => 'fremlejer'),
			'vaerelse', false, 'fremtidige'));
		if(count($fremlejere) > 0){
			foreach($fremlejere as $bruger) {
				// hvis indflytningsdato eller navn er samme, må det formodes
				// at være den samme fremlejer
				if($bruger['indflytning'] == $ind || $bruger['navn'] == $navn) {
					$fremlejer_allerede_oprettet = true;
					opdater_fremlejer_bruger_fra_kabas_lejemaal(
						$lejemaal, $bruger['brugernavn']);
				}
			}
		}

		// opret fremlejer, hvis ikke oprettet i forvejen, og fremlejeperioden
		// ikke er afsluttet endnu
		if (!$fremlejer_allerede_oprettet
			&& (!$ud || strtotime($ud) > time())) {
			opret_fremlejer_bruger_fra_kabas_lejemaal($lejemaal);
		}
	}
}
