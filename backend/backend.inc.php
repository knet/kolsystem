<?php
$configfile = '/kolsystem/config.php';
if (!file_exists($configfile)) {
	echo "Missing config file: ". $configfile;
	exit();
}
require($configfile);

class backend_database_exception extends Exception { }
class backend_parameter_fejl_database_exception
	extends backend_database_exception { }
class backend_trigger_database_exception extends backend_database_exception { }
class forkert_input_exception extends Exception { }

/**
 * Lav et database kald. Erstat alle spørgsmålstegn med værdierne i parameter-
 * arrayet med quotetegn omkring. Brug PHP værdien null til at sætte et felt til
 * SQL værdien NULL. Brug kun parameter-arrayet til data værdier, og ikke
 * tabelnavn eller feltnavne. Disse bør i stedet blive tjekket mod whitelist og
 * indsat direkte i forespørgselsstrengen q.
 *
 * Kan kaste en DatabaseException.
 * Kan kaste en TriggerDatabaseException.
 *
 * @param string $q MySQL forespørgselsstreng
 * @param $parametre Array af værdier der skal erstatte
 *	spørgsmålstegnene
 * @return Et array med maps
 */
function backend_dbquery($q, $parametre = null) {
	static $mysqlhandle;

	// forbind hvis ikke forbundet allerede
	if($mysqlhandle == null || !mysql_ping($mysqlhandle)) {
		$mysqlhandle = @mysql_connect(KOLSYSTEM_DB_HOST, KOLSYSTEM_DB_USERNAME,
			KOLSYSTEM_DB_PASSWORD);
		if(!$mysqlhandle) {
			throw new backend_database_exception('could not connect to db');
		}
		if(!mysql_select_db(KOLSYSTEM_DB_NAME)) {
			throw new backend_database_exception(mysql_error($mysqlhandle));
		}

		mysql_set_charset('utf8', $mysqlhandle);
	}

	// indsæt værdier
	if($parametre != null) {
		$qx = explode('?', $q);
		if(count($qx) - 1 != count($parametre))
			throw new backend_parameter_fejl_database_exception();

		$q = '';
		for($i = 0; $i < count($parametre); $i++) {
			if($parametre[$i] === null) $s = 'NULL';
			else $s = "'".mysql_real_escape_string($parametre[$i])."'";
			$q .= $qx[$i] . $s;
		}
		$q .= $qx[count($qx)-1];
	}

	// kør forespørgsel
	$result = mysql_query($q, $mysqlhandle);
	if($result === false) {
		if(strpos(mysql_error($mysqlhandle), 'TRIGGER_DUMMY_') !== false) {
			// exceptions kastet fra en trigger er lavet ved at lave calls
			// til ikke-eksisterende dummy stored procedures, der har
			// prefixene TRIGGER_DUMMY_
			preg_match('/TRIGGER_DUMMY_(.*?) /', mysql_error($mysqlhandle), $r);
			throw new backend_trigger_database_exception($r[1]);
		} else
			throw new backend_database_exception(mysql_error($mysqlhandle));
		// her skal måske kastes flere forskellige exceptions efter hvad
		// fejltypen er
	}

	if($result === true) {
		return;
	}

	// hent alle returværdier til et simpelt PHP array
	$ret = array();
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
	}
	return $ret;
}

/**
 * Genrerer et password der er rimeligt sikkert, men alligevel nemt at huske.
 *
 * @return Et genereret password
 */
function backend_generer_passwd() {
	// konsonanter
	$k = array('q','w','r','t','p','d','f','g','h','j','k','c','v','b','n','m',
		'th','ch','cr','br','ch','ph');
	// vokaler
	$v = array('a','u','e','i','y','ea');

	// generer et grundpassword bestående af skiftevis konsonanter og vokaler
	$passwd = '';
	while(strlen($passwd) < 8) {
		$passwd .= $v[mt_rand(0, count($v) - 1)];
		$passwd .= $k[mt_rand(0, count($k) - 1)];
	}

	// vælg to positioner i password strengen
	$x1 = mt_rand(0, strlen($passwd)-1);
	$x2 = mt_rand(0, strlen($passwd)-1);

	// de to positioner må ikke være ens
	while($x2 == $x1) $x2 = mt_rand(0, strlen($passwd)-1);

	// udskift tegn på disse positioner med henholsvis tal og caps
	$passwd = substr_replace($passwd, mt_rand(2, 9), $x1, 1);
	$passwd = substr_replace($passwd,
		strtoupper(substr($passwd, $x2, 1)), $x2, 1);

	return $passwd;
}

/**
 * Tjekker om et brugernavn er ledigt.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @return true hvis ledigt, false ellers
 */
function backend_brugernavn_ledigt($brugernavn) {
	// tjek i brugere tabellen
	$r = backend_dbquery(
		'SELECT brugernavn FROM brugere WHERE brugernavn LIKE ?',
		array($brugernavn));
	if(count($r) != 0) return false;
	
	// tjek i gamle_beboere indenfor 3 måneder
	$r = backend_dbquery(
		'SELECT brugernavn FROM tidligere_beboere '
		.'WHERE brugernavn LIKE ? '
		.'AND (to_days(curdate())-to_days(udflytning))<90',
		array($brugernavn));
	if(count($r) != 0) return false;
	
	return true;
}

/**
 * Genrerer et ledigt brugernavn af initialer af fulde navn med prefiks "nyk".
 *
 * @param string $fulde_navn Brugerens fulde navn
 * @return Et genereret brugernavn
 */
function backend_generer_brugernavn($fulde_navn) {
	$fulde_navn = str_replace('æ', 'a', $fulde_navn);
	$fulde_navn = str_replace('Æ', 'a', $fulde_navn);
	$fulde_navn = str_replace('ø', 'o', $fulde_navn);
	$fulde_navn = str_replace('Ø', 'o', $fulde_navn);
	$fulde_navn = str_replace('å', 'å', $fulde_navn);
	$fulde_navn = str_replace('Å', 'å', $fulde_navn);
	$fulde_navn = str_replace('ö', 'o', $fulde_navn);
	$fulde_navn = str_replace('Ö', 'o', $fulde_navn);
	$fulde_navn = preg_replace('/([^\w ])/', '', $fulde_navn);

	// opdel fornavn, mellemnavn og efternavne
	$q = explode(' ', strtolower($fulde_navn));

	// brugernavn kandidater
	$k = array();
	if(count($q) == 1) { // kun fornavn var givet
		$k[] = substr($q[0], 0, 3); // kandidat: første tre bogstaver
		if(strlen($q[0]) < 6) $k[] = $q[0]; // kandidat: hele navn
		$k[] = substr($q[0], 0, 4); // kandidat: første fire bogstaver
		$k[] = substr($q[0], 0, 5); // kandidat: første fem bostaver
	} elseif(count($q) == 2) { // fornavn og efternavn
		// kandidat: første bogstave af fornavn samt to første fra efternavn
		$k[] = substr($q[0], 0, 1) . substr($q[1], 0, 2);
		// kandidat: første to bogstaver af fornavn samt første fra efternavn
		$k[] = substr($q[0], 0, 2) . substr($q[1], 0, 1);
		$k[] = substr($q[0], 0, 2) . substr($q[1], 0, 2); // ...
		$k[] = substr($q[0], 0, 1) . substr($q[1], 0, 3);
		$k[] = substr($q[0], 0, 3) . substr($q[1], 0, 1);
	} elseif(count($q) >= 3) {
		$k[] = substr($q[0], 0, 1) . substr($q[1], 0, 1)
			. substr($q[count($q) - 1], 0, 1);
		if(count($q) == 4) $k[] = substr($q[0], 0, 1) . substr($q[1], 0, 1)
				. substr($q[2], 0, 1) . substr($q[3], 0, 1);
		$k[] = substr($q[0], 0, 2) . substr($q[count($q) - 1], 0, 1);
		$k[] = substr($q[0], 0, 1) . substr($q[count($q) - 1], 0, 2);
	} else $k[] = '1';

	// filtrer latterlige navne
	$k = array_values(array_diff($k,
			array('root', 'gud', 'lort', 'pik', 'pikr', 'dad',
				'hak', 'hec', 'homo', 'hom', 'kilo', 'adm', 'pis')));

	// sæt nyk foran alle kandidater
	for($i = 0; $i < count($k); $i++) $k[$i] = 'nyk' . $k[$i];

	// vælg et ledigt brugernavn blandt kandidater
	$brugernavn = null;
	for($i = 0; $brugernavn == null && $i < count($k); $i++)
		if(backend_brugernavn_ledigt($k[$i])) $brugernavn = $k[$i];

	// hvis intet var fundet, prøv da at sæt tal bagpå
	for($i = 1; $brugernavn == null; $i++)
		if(backend_brugernavn_ledigt($k[0] . $i)) $brugernavn = $k[0] . $i;

	return $brugernavn;
}

/**
 * Laver brugernavn og password og opretter i databasen. Sætter brugeren på
 * default grupperne.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $vaerelse
 * @param string $navn
 * @param string $indflytning Dato i format "YYYY-MM-DD"
 * @param string $udflytning Dato i format "YYYY-MM-DD". Her oftest null.
 * @param string $kab_lejemaal_id Unikt ID for lejemål fra KABAS
 * @param string $lejer_type 'lejer1', 'lejer2' (lejl.) eller 'fremlejer'
 * @return Brugernavnet på den oprettede bruger
 */
function backend_ny_bruger($vaerelse, $navn, $indflytning, $udflytning,
	$kab_lejemaal_id, $lejer_type) {

	$brugernavn = backend_generer_brugernavn($navn);

	$q = 'INSERT INTO brugere SET brugernavn=?, vaerelse=?, '
		.'navn=?, indflytning=?, udflytning=?, kab_lejemaal_id=?, lejer_type=?';
	$p = array($brugernavn, $vaerelse, $navn, $indflytning, $udflytning,
		$kab_lejemaal_id, $lejer_type);
	backend_dbquery($q, $p);

	$password = backend_generer_passwd();
	backend_set_brugerdata($brugernavn, array('password' => $password));

	// tilmeld til default grupper
	backend_set_gruppemedlemskab($brugernavn, 'nyk_alle_beboere');
	backend_set_gruppemedlemskab($brugernavn, 'nyk_meddelelse');
	backend_set_gruppemedlemskab($brugernavn, 'nyk_kc_reklame');
		
	// tilmeld til lejlighedsgruppe hvis lejlighed, ellers gang-liste
	$r = backend_dbquery('SELECT vaerelse_type '
		.'FROM vaerelser WHERE vaerelse = ?', array($vaerelse));
	if($r[0]['vaerelse_type'] == 'lejlighed') {
		backend_set_gruppemedlemskab($brugernavn, 'nyk_lejligheder');
	} else if($r[0]['vaerelse_type'] == 'vaerelse'){
		$bogstav = substr($vaerelse, 0, 1);
		$lige = substr($vaerelse, 1, 2) % 2 == 0;
		if(stripos('ab', $bogstav) !== false && !$lige) $g = 'nyk_ab_ulige';
		if(stripos('ab', $bogstav) !== false && $lige) $g = 'nyk_ab_lige';
		if(stripos('cd', $bogstav) !== false && !$lige) $g = 'nyk_cd_ulige';
		if(stripos('cd', $bogstav) !== false && $lige) $g = 'nyk_cd_lige';
		if(stripos('ef', $bogstav) !== false && !$lige) $g = 'nyk_ef_ulige';
		if(stripos('ef', $bogstav) !== false && $lige) $g = 'nyk_ef_lige';
		if(stripos('gh', $bogstav) !== false && !$lige) $g = 'nyk_gh_ulige';
		if(stripos('gh', $bogstav) !== false && $lige) $g = 'nyk_gh_lige';
		if(stripos('jk', $bogstav) !== false && !$lige) $g = 'nyk_jk_ulige';
		if(stripos('jk', $bogstav) !== false && $lige) $g = 'nyk_jk_lige';
		if(stripos('lm', $bogstav) !== false && !$lige) $g = 'nyk_lm_ulige';
		if(stripos('lm', $bogstav) !== false && $lige) $g = 'nyk_lm_lige';
		if(stripos('no', $bogstav) !== false && !$lige) $g = 'nyk_no_ulige';
		if(stripos('no', $bogstav) !== false && $lige) $g = 'nyk_no_lige';
		if(stripos('st', $bogstav) !== false && !$lige) $g = 'nyk_st_ulige';
		if(stripos('st', $bogstav) !== false && $lige) $g = 'nyk_st_lige';
		if(stripos('pr', $bogstav) !== false && !$lige) $g = 'nyk_pr_ulige';
		if(stripos('pr', $bogstav) !== false && $lige) $g = 'nyk_pr_lige';
		if($g) backend_set_gruppemedlemskab($brugernavn, $g);
	}

	return $brugernavn;
}

/**
 * Opdaterer data for den givne bruger ud fra det associative array $data. Hvis
 * data er tomt sker der intet. Hvis data indeholder invalide nøgler eller
 * invalid data, ignoreres disse nøgler eller dette data.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn Nuværende brugernavn på pågældende bruger.
 * @param array $data Associativt array hvor følgende nøgler er mulige:
 *  - brugernavn (string som value)
 *  - vaerelse (string som value)
 *  - navn (string som value)
 *  - password (string som value)
 *  - email (string som value)
 *  - mobilnummer (string som value)
 *  - hjemmeside (string som value)
 *  - spaerret_net_konto (boolean som value)
 *	- skjult_navn (boolean som value)
 *  - skjult_email (boolean som value)
 *  - indflytning (string med dato i format "YYYY-MM-DD" som value)
 *  - udflytning (string med dato i format "YYYY-MM-DD" som value)
 *  - kab_lejemaal_id (string som value)
 *  - lejer_type (string som value, enten "lejer1", "lejer2" eller
 *     "fremlejer")
 */
function backend_set_brugerdata($brugernavn, $data) {
	$q = '';

    // hvis tom email angivet, skal bruges null-værdi
    if(array_key_exists('email', $data) && $data['email']=='')
        $data['email'] = null;

	// sæt eventuelle string felter
	$string_felter = array('brugernavn', 'vaerelse', 'navn', 'email',
		'mobilnummer', 'hjemmeside', 'kab_lejemaal_id', 'lejer_type');
	foreach($string_felter as $felt) {
		if(array_key_exists($felt, $data)) {
			if($q != '') $q .= ', ';
			$q .= $felt.'=?';
			$p[] = $data[$felt];
		}
	}

	// sæt eventuelt password
	if(array_key_exists('password', $data)) {
		if($q != "") $q .= ', ';
		
		// gem cleartext (udkommenteret, da vi ikke gemmer disse længere)
		// $q .= 'password_cleartext=?, ';
        // $p[] = $data['password'];
		
        $q .= 'password_nt=?, password_unix=?';
        // generer NT-hash
		$p[] = strtoupper(hash('md4', iconv('UTF-8', 'UTF16LE',
			$data['password'])));

		// generer Unix hash, enten via eksisterende salt, eller via ny salt
		$r = backend_dbquery('SELECT password_unix '
			.'FROM brugere WHERE brugernavn = ?',
			array($brugernavn));
		$salt = '';
		if(count($r) > 0 && strpos($r[0]['password_unix'], '$1$') === 0) {
			$salt = $r[0]['password_unix'];
		} else {
			$salt = '$1$';
			$chars = 'abcdefghijklmnopqrstuvwxyz'
			 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890./';
			for($i=0; $i < 8; $i++)
				$salt .= $chars[rand(0,strlen($chars)-1)];
		}
		$p[] = crypt($data['password'], $salt);
	}

	// sæt eventuelle boolean felter
	$boolean_felter = array('skjult_navn', 'skjult_email',
		'spaerret_net_konto');
	foreach($boolean_felter as $felt) {
		if(array_key_exists($felt, $data)) {
			if($data[$felt] === true || $data[$felt] === false) {
				if($q != '') $q .= ', ';
				$q .= $felt . '=' . ($data[$felt] ? '1' : '0');
			}
		}
	}

	// sæt eventuelle dato felter
	$dato_felter = array('indflytning', 'udflytning');
	foreach($dato_felter as $felt) {
		if(array_key_exists($felt, $data)) {
			if(strtotime($data[$felt]) !== false || $data[$felt] === null) {
				if($q != '') $q .= ', ';
				$q .= $felt.'=?';
				$p[] = $data[$felt];
			}
		}
	}

	if($q != '') {
		$q = "UPDATE brugere SET $q WHERE brugernavn LIKE ?";
		$p[] = $brugernavn;
		backend_dbquery($q, $p);
	}

	// TODO sync passwd med knet
}

/**
 * Søg efter en liste af nuværende brugere / beboere.
 *
 * Kan kaste en DatabaseException.
 *
 * @param map med følgende keys, values
 *  - brugernavn, brugernavnet på beboer
 *  - navn, navn eller del af navn på beboer
 *  - vaerelse, f.eks. "L03" eller "03"
 *  - etage, 0 eller 1
 *  - vaerelse_type, "lejlighed", "vaerelse", "begge" (dvs. både lejligheder og
 *     værelser), "koekken" eller "andet"
 *  - blok, f.eks. "AB", "CD" eller "JK"
 *  - kab_lejemaal_id
 *  - lejer_type
 * @param string $sorterEfter Felt som der skal sorteres efter
 * @param boolean $asc Faldende sorteringen (true) eller stigende (false)
 * @param string $status Enten 'nuvaerende', 'fremtidige', 'udflytninger', 
 *   'udflyttede' eller 'alle'
 * @return Liste af maps med følgende keys: brugernavn, vaerelse, navn, email,
 *	mobilnummer, hjemmeside, net_tilmeldt_dato, net_tilmeldt_dato_kabstatus,
 *  spaerret_net_konto, skjult_navn, skjult_email, indflytning, udflytning,
 *  kab_lejemaal_id, lejer_type ("lejer1", "lejer2" eller "fremlejer")
 */
function backend_hent_brugere($data = array(), $sorterEfter = "vaerelse",
	$asc = false, $status = 'nuvaerende') {

	$q = 'SELECT brugernavn, vaerelse, navn, password_unix, password_nt, '
		.'email, mobilnummer, hjemmeside, net_tilmeldt_dato, '
		.'net_tilmeldt_dato_kabstatus, spaerret_net_konto, skjult_navn, '
		.'skjult_email, indflytning, udflytning, kab_lejemaal_id, lejer_type '
		.'FROM brugere NATURAL JOIN vaerelser WHERE 1=1 ';
	$p = array();

	// sæt eventuelle string-kompatible filtre
	$string_felter = array('brugernavn', 'kab_lejemaal_id', 'lejer_type');
	foreach($string_felter as $felt) {
		if(isset($data[$felt])) {
			$q .= 'AND '.$felt.' LIKE ? ';
			$p[] = $data[$felt];
		}
	}

	// sæt andre eventuelle løsere string-kompatible filtre
	$string_felter = array('vaerelse', 'email', 'mobilnummer', 'navn');
	foreach($string_felter as $felt) {
		if(isset($data[$felt])) {
			$q .= 'AND '.$felt.' LIKE ? ';
			$p[] = '%'.$data[$felt].'%';
		}
	}

	if(isset($data['etage'])) {
		// lige numre er etage 0, ulige numre er etage 1
		if($data['etage'] === 0) {
			$q .= "AND (SUBSTRING(vaerelse, 2, 2) REGEXP '^[0-9].$' "
				." AND MOD(SUBSTRING(vaerelse, 2, 2), 2) = 0) ";
		} elseif($data['etage'] === 1) {
			$q .= "AND (SUBSTRING(vaerelse, 2, 2) REGEXP '^[0-9].$' "
				." AND MOD(SUBSTRING(vaerelse, 2, 2), 2) = 1) ";
		}
	}

	$whitelist = array('lejlighed', 'vaerelse', 'begge', 'koekken', 'andet');
	if(isset($data['vaerelse_type']) &&
		in_array($data['vaerelse_type'], $whitelist)) {
		if($data['vaerelse_type'] == 'begge')
			$q .= "AND (vaerelse_type='vaerelse' "
				."OR vaerelse_type='lejlighed') ";
		else
			$q .= "AND vaerelse_type = '".$data['vaerelse_type']."'";
	}

	if(isset($data['blok'])) {
		$tmp = "AND (vaerelse_type = 'vaerelse' OR "
			."vaerelse_type = 'lejlighed') ";
		if($data['blok'] == "AB") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'A' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'B' ";
		} elseif($data['blok'] == "CD") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'C' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'D' ";
		} elseif($data['blok'] == "EF") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'E' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'F' ";
		} elseif($data['blok'] == "GH") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'G' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'H' ";
		} elseif($data['blok'] == "JK") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'J' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'K' ";
		} elseif($data['blok'] == "LM") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'L' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'M' ";
		} elseif($data['blok'] == "NO") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'N' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'O' ";
		} elseif($data['blok'] == "PR") {
			$q .= $tmp."AND SUBSTRING(vaerelse, 1, 1) = 'P' OR "
				."SUBSTRING(vaerelse, 1, 1) = 'R' ";
		} else {
			$q .= "AND FALSE ";
		}
	}
	
	if($status == 'fremtidige') {
		$q .= 'AND indflytning > DATE(NOW()) ';
	} else if($status == 'udflyttede') {
		$q .= 'AND udflytning <= DATE(NOW()) ';
	} else if($status == 'udflytninger') {
		$q .= 'AND (NOT udflytning IS NULL) AND udflytning >= DATE(NOW()) ';
    } else if($status == 'alle') {
    } else {
		$q .= 'AND (indflytning <= DATE(NOW()) OR indflytning IS NULL) ';
		$q .= 'AND (udflytning >= DATE(NOW()) OR udflytning IS NULL) ';
	}

	$whitelist = array('navn', 'vaerelse', 'vaerelse_type');
	if(in_array($sorterEfter, $whitelist))
		$q .= " ORDER BY $sorterEfter ";

	if($asc == true)
		$q .= "ASC ";

	return backend_dbquery($q, $p);
}

/**
 * Tilmeld bruger til kollegiets netværk
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 */
function backend_tilmeld_bruger_til_net($brugernavn) {
	return backend_dbquery("UPDATE brugere SET net_tilmeldt_dato = NOW() "
		."WHERE brugernavn LIKE ?", array($brugernavn));
}

/**
 * Frameld bruger fra kollegiets netværk.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 */
function backend_frameld_bruger_fra_net($brugernavn) {
	backend_dbquery("UPDATE brugere SET net_tilmeldt_dato = NULL "
		."WHERE brugernavn LIKE ?", array($brugernavn));
}

/**
 * Slet bruger fra brugere-tabellen.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 */
function backend_slet_bruger($brugernavn) {
	backend_dbquery("DELETE FROM brugere WHERE brugernavn = ?",
		array($brugernavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @param string $password
 * @return boolean Om brugernavn og password kombination er rigtig
 */
function backend_valider_bruger_og_password($brugernavn, $password) {
	$r = backend_dbquery(
		'SELECT password_unix '
		.'FROM brugere WHERE brugernavn = ? AND (udflytning >= NOW() OR udflytning IS NULL) AND (indflytning <= NOW() OR indflytning IS NULL)',
		array($brugernavn));
	if(count($r) != 1) return false;
	if(crypt($password, $r[0]['password_unix']) === $r[0]['password_unix'])
		return true;
	return false;
}

/**
 * Kan kaste en DatabaseException.
 *
 * @return Liste af maps med følgende keys: gruppenavn, beskrivelse,
 *   mail_liste_navn, mail_skriverettigheder, mail_obligatorisk, mail_footer
 */
function backend_hent_grupper() {
	return backend_dbquery(
		'SELECT gruppenavn, beskrivelse, mail_liste_navn, '
		.'mail_skriverettigheder, mail_obligatorisk, mail_footer '
		.'FROM grupper', array());
}

/**
 * Kan kaste en DatabaseException.
 *
 * @return Liste af maps med følgende keys: beskrivelse, mail_liste_navn,
 *  mail_skriverettigheder, mail_obligatorisk, mail_footer
 */
function backend_hent_gruppe($gruppenavn) {
	$r = backend_dbquery(
		'SELECT gruppenavn, beskrivelse, mail_liste_navn, '
		.'mail_skriverettigheder, mail_obligatorisk, mail_footer '
		.'FROM grupper WHERE gruppenavn LIKE ?', array($gruppenavn));
	if(count($r) != 1) throw new forkert_input_exception();
	return $r[0];
}

/**
 * Tilmelder en bruger til en gruppe. Hvis brugeren allerede er tilmeldt,
 * opdateres brugeren med givne data..
 *
 * Kan kaste en DatabaseException.
 * Kan kaste en forkert_input_exception.
 *
 * @param string $brugernavn
 * @param string $gruppenavn
 * @param array $data Associativt array hvor følgende nøgler er mulige:
 *  - gruppeadmin (boolean som value)
 *  - mail_forfatter (boolean som value)
 *  - mail_modtag (boolean som value)
 */
function backend_set_gruppemedlemskab($brugernavn, $gruppenavn,
	$data = array()) {

	// angivet forkert brugernavn?
	if(count(backend_dbquery(
		'SELECT brugernavn FROM brugere WHERE brugernavn = ?',
		array($brugernavn))) != 1)
		throw new forkert_input_exception();

	$q = '';
	$p = array();

	// sæt eventuelle boolean felter
	$boolean_felter = array('gruppeadmin', 'mail_forfatter',
		'mail_modtag');
	foreach($boolean_felter as $felt) {
		if(array_key_exists($felt, $data)) {
			if($data[$felt] === true || $data[$felt] === false) {
				if($q != '') $q .= ', ';
				$q .= $felt . '=' . ($data[$felt] ? '1' : '0');
			}
		}
	}

	// findes gruppemedlemskabet i forvejen?
	$r = backend_dbquery('SELECT brugernavn FROM gruppemedlemskaber WHERE '
		.'brugernavn LIKE ? AND gruppenavn LIKE ?',
		array($brugernavn, $gruppenavn));
	if(count($r) > 0) {
		// er der ændret data?
		if($q != '') {
			$q .= ' WHERE brugernavn = ? AND gruppenavn = ?';
			$p[] = $brugernavn;
			$p[] = $gruppenavn;

			backend_dbquery('UPDATE gruppemedlemskaber SET ' . $q, $p);
		}
	} else {
		if($q != '') $q .= ', ';
		$q .='oprettet=NOW()';

		if($q != '') $q .= ', ';
		$q .= 'brugernavn=?, gruppenavn = ?';
		$p[] = $brugernavn;
		$p[] = $gruppenavn;

		backend_dbquery('INSERT INTO gruppemedlemskaber SET ' . $q, $p);
	}
}


/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @param string $gruppenavn
 */
function backend_slet_gruppemedlemskab($brugernavn, $gruppenavn) {
	backend_dbquery(
		"DELETE FROM gruppemedlemskaber WHERE brugernavn LIKE ? AND "
		."gruppenavn LIKE ?", array($brugernavn, $gruppenavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $gruppenavn
 * @return Liste af maps med følgende keys: brugernavn, oprettet, gruppeadmin,
 *   mail_forfatter, mail_modtag
 */
function backend_hent_gruppe_medlemmer($gruppenavn) {
	return backend_dbquery('SELECT brugernavn, oprettet, gruppeadmin, '
		.'mail_forfatter, mail_modtag, vaerelse, navn, email '
		.'FROM gruppemedlemskaber NATURAL JOIN brugere '
        .'WHERE gruppenavn LIKE ? AND (udflytning >= NOW() '
        .'OR udflytning IS NULL) AND (indflytning <= NOW() '
        .'OR indflytning IS NULL) ORDER BY vaerelse', array($gruppenavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @return Liste af maps med følgende keys: brugernavn, oprettet, gruppeadmin,
 *   mail_forfatter, mail_modtag, beskrivelse, mail_liste_navn,
 *   mail_skriverettigheder, mail_obligatorisk
 */
function backend_hent_brugers_medlemskaber($brugernavn) {
	return backend_dbquery('SELECT gruppenavn, oprettet, gruppeadmin, '
		.'mail_forfatter, mail_modtag, beskrivelse, mail_liste_navn, '
		.'mail_skriverettigheder, mail_obligatorisk '
		.'FROM gruppemedlemskaber NATURAL JOIN grupper '
		.'WHERE brugernavn LIKE ? ORDER BY mail_liste_navn',
		array($brugernavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @return Liste af maps med følgende keys: brugernavn, oprettet,
 *   mail_forfatter, mail_modtag, beskrivelse, mail_liste_navn,
 *   mail_skriverettigheder, mail_obligatorisk
 */
function backend_hent_brugers_admin_medlemskaber($brugernavn) {
	return backend_dbquery('SELECT gruppenavn, oprettet, mail_forfatter, '
		.'mail_modtag, beskrivelse, mail_liste_navn, '
		.'mail_skriverettigheder, mail_obligatorisk '
		.'FROM gruppemedlemskaber NATURAL JOIN grupper '
		.'WHERE gruppeadmin = TRUE AND brugernavn LIKE ? '
		.'ORDER BY mail_liste_navn',
		array($brugernavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn
 * @param string $gruppenavn
 * @return Liste af maps med følgende keys: oprettet, gruppeadmin,
 *   mail_forfatter, mail_modtag.
 */
function backend_hent_gruppemedlemskab($brugernavn, $gruppenavn) {
	$r = backend_dbquery('SELECT oprettet, gruppeadmin, '
		.'mail_forfatter, mail_modtag '
		.'FROM gruppemedlemskaber '
		.'WHERE brugernavn LIKE ? AND gruppenavn LIKE ?',
		array($brugernavn, $gruppenavn));
	if(count($r) != 1) throw new forkert_input_exception();
	return $r[0];
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $gruppenavn
 * @return Liste af maps med følgende keys: brugernavn, oprettet, gruppeadmin,
 *   mail_forfatter, mail_modtag
 */
function backend_hent_gruppe_medlemmer_eksterne($gruppenavn) {
	return backend_dbquery('SELECT email, mail_forfatter, beskrivelse '
		.'FROM gruppemedlemskaber_eksterne '
		.'WHERE gruppenavn LIKE ?', array($gruppenavn));
}

/**
 * Tilmelder en email addresse hvor der ikke findes en tilsvarende bruger, til
 * en gruppe. Hvis email addressen allerede er tilmeldt, opdateres den med givne
 * data.
 *
 * Kan kaste en DatabaseException.
 *
 * @param string $email
 * @param string $gruppenavn
 * @param array $data Associativt array hvor følgende nøgler er mulige:
 *  - mail_forfatter (boolean som value)
 *  - beskrivelse (string som value)
 */
function backend_set_gruppemedlemskab_eksternt($email, $gruppenavn,
	$data = array()) {

	$q = '';
	$p = array();

	// sæt eventuelt mail_forfatter
	$felt = 'mail_forfatter';
	if(array_key_exists($felt, $data)) {
		if($data[$felt] === true || $data[$felt] === false) {
			if($q != '') $q .= ', ';
			$q .= $felt . '=' . ($data[$felt] ? '1' : '0');
		}
	}

	// sæt eventuelle beskrivelse felt
	$felt = 'beskrivelse';
	if(array_key_exists($felt, $data)) {
		if($q != '') $q .= ', ';
		$q .= $felt.'=?';
		$p[] = $data[$felt];
	}

	// findes gruppemedlemskabet i forvejen?
	$r = backend_dbquery(
		'SELECT email FROM gruppemedlemskaber_eksterne '
		.'WHERE email LIKE ? AND gruppenavn LIKE ?',
		array($email, $gruppenavn));
	if(count($r) > 0) {
		// er der ændret data?
		if($q != '') {
			$q .= ' WHERE email = ? AND gruppenavn = ?';
			$p[] = $email;
			$p[] = $gruppenavn;

			backend_dbquery('UPDATE gruppemedlemskaber_eksterne SET ' . $q, $p);
		}
	} else {
		if($q != '') $q .= ', ';
		$q .= 'email=?, gruppenavn = ?';
		$p[] = $email;
		$p[] = $gruppenavn;

		$q = 'INSERT INTO gruppemedlemskaber_eksterne SET ' . $q;
		backend_dbquery($q, $p);
	}
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $email
 * @param string $gruppenavn
 */
function backend_slet_gruppemedlemskab_eksternt($email, $gruppenavn) {
	backend_dbquery(
		"DELETE FROM gruppemedlemskaber_eksterne WHERE email LIKE ? AND "
		."gruppenavn LIKE ?", array($email, $gruppenavn));
}

/**
 * Kan kaste en DatabaseException.
 *
 * @param string $brugernavn_eksisterende Brugernavnet fra det gamle værelse,
 *   som bliver bevaret.
 * @param string $brugernavn_midlertidigt Brugernavn der er blevet oprettet af
 *   systemet for brugeren på det nye værelse. Dette brugernavn bliver slettet.
 */
function backend_intern_flytning($brugernavn_eksisterende,
		$brugernavn_midlertidigt) {
	/*
	TODO:
	
	Noter det nye kab_lejemaal_id, indflytning, lejertype, vaerelse og
	udflytning fra den nye bruger der er blevet oprettet

	Slet den nye bruger.

	Sæt det nye kab_lejemaal_id, indflytning, lejertype, vaerelse og udflytning
	ind i den gamle bruger
	*/
}

/**
 * Sætter batch scripts i gang som skal opdatere lokale password filer og lign.
 */
function backend_opdater_datafiler() {
	shell_exec("sudo ".BATCHES_PATH."opdater_password_filer.php > /dev/null &");
	shell_exec("sudo ".BATCHES_PATH."mailliste_batch.php > /dev/null &");
}
