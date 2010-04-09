<?php

echo "Disse unittests kan ikke koeres paa produktionssystem. Saet "
	."passende \ntestvaerdier i config.php. AL DATA vil blive slettet! "
	."Vil du fortsaette? (y/n): ";
$stdin = fopen('php://stdin', 'r');
$s = fgets($stdin);
if(trim($s) != "y") {
	echo "Afslutter da der ikke blev bekraeftet\n";
	exit();
}

require("../backend/backend.inc.php");

function fejl($linje) {
	echo "fejl ved linje ".$linje."\n";
	exit();
}

function dbquery_test() {
	echo "Tester dbquery: ";

	try {
		$r = backend_dbquery("SELECT ? AS testfield", array('testdata'));
		if($r[0]['testfield'] != 'testdata') fejl(__LINE__);
	} catch(Exception $e) {
		fejl(__LINE__);
	}

	$test = false;
	try {
		$r = backend_dbquery("syntaxerror");
	} catch(Exception $e) {
		$test = true;
	}
	if($test == false) fejl(__LINE__);

	echo "ok\n";
}

function generer_passwd_test() {
	echo "Tester generer_passwd: ";
	if(strlen(backend_generer_passwd()) < 3) fejl(__LINE__);
	if(strlen(backend_generer_passwd()) < 3) fejl(__LINE__);
	if(strlen(backend_generer_passwd()) < 3) fejl(__LINE__);
	if(backend_generer_passwd() == backend_generer_passwd()) fejl(__LINE__);
	if(backend_generer_passwd() == backend_generer_passwd()) fejl(__LINE__);
	if(backend_generer_passwd() == backend_generer_passwd()) fejl(__LINE__);
	echo "ok\n";
}

function brugernavn_ledigt_test() {
	echo "Tester brugernavn_ledigt: ";

	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykoptg'");
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nyktte'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");

	if(backend_brugernavn_ledigt('nykledig') != true) fejl(__LINE__);

	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L88', "
		."vaerelse_type='vaerelse'");
	backend_dbquery("INSERT INTO brugere SET vaerelse = 'L88', brugernavn='nykoptg', "
		."navn='test'");
	if(backend_brugernavn_ledigt('nykoptg') != false) fejl(__LINE__);
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykoptg'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");

	echo "ok\n";
}

function generer_brugernavn_test() {
	echo "Tester generer_brugernavn: ";


	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykarb'");
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykalb'");
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykabo'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");

	if(backend_generer_brugernavn('Allan Riordan Boll') != 'nykarb') fejl(__LINE__);
	if(backend_generer_brugernavn('Allan Boll') != 'nykabo') fejl(__LINE__);

	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L88', "
		."vaerelse_type='vaerelse'");
	backend_dbquery("INSERT INTO brugere SET vaerelse = 'L88', brugernavn='nykarb', "
		."navn='x'");
	if(backend_generer_brugernavn('Allan Riordan Boll') != 'nykalb') fejl(__LINE__);

	backend_dbquery("INSERT INTO brugere SET vaerelse = 'L88', brugernavn='nykalb', "
		."navn='x'");
	if(backend_generer_brugernavn('Allan Riordan Boll') != 'nykabo') fejl(__LINE__);

	backend_dbquery("INSERT INTO brugere SET vaerelse = 'L88', brugernavn='nykabo', "
		."navn='x'");
	if(backend_generer_brugernavn('Allan Riordan Boll') != 'nykarb1') fejl(__LINE__);

	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykarb'");
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykalb'");
	backend_dbquery("DELETE FROM brugere WHERE brugernavn='nykabo'");

	echo "ok\n";
}

function ny_bruger_test() {
	echo "Tester ny_bruger: ";

	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn2 Testefternavn2'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");
	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L88', "
		."vaerelse_type='vaerelse'");

	$r = backend_ny_bruger("L88", "Testfornavn Testefternavn", "2007-07-01", null,
		"0888-3-18", "lejer1");
	if($r != 'nyktte') fejl(__LINE__);

	$r = backend_dbquery(
		"SELECT * FROM brugere WHERE navn='Testfornavn Testefternavn'");
	if(count($r) != 1) fejl(__LINE__);

	$test = false;
	try {
		backend_ny_bruger("X03", "Testfornavn2 Testefternavn2", "2008-02-15", null,
			"0238-5-11", "lejer1");
	} catch(backend_database_exception $e) {
		if(!strstr("foreign key constraint", $e)) $test = true;
	}
	if(!$test) fejl(__LINE__);

	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn2 Testefternavn2'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");
	echo "ok\n";
}

function setup_testdata1() {
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");
	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L88', "
		."vaerelse_type='vaerelse'");
	$r = backend_ny_bruger("L88", "Testfornavn Testefternavn", "2007-07-01", null,
		"0888-3-18", "lejer1");
	if($r != 'nyktte') fejl(__LINE__);

	backend_dbquery("DELETE FROM gruppemedlemskaber WHERE brugernavn LIKE 'nyktte'");
	backend_dbquery("DELETE FROM grupper WHERE gruppenavn LIKE 'testgruppe'");
	backend_dbquery("INSERT INTO grupper SET gruppenavn = 'testgruppe'");

}

function teardown_testdata1() {
	backend_dbquery("DELETE FROM gruppemedlemskaber WHERE brugernavn LIKE 'nyktte'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L88'");
	backend_dbquery("DELETE FROM grupper WHERE gruppenavn LIKE 'testgruppe'");
}

function set_brugerdata_test() {
	echo "Tester set_brugerdata: ";

	setup_testdata1();

	// tjekt et string felt
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['email'] != null) fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('email' => 'test@test.test'));
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['email'] != 'test@test.test') fejl(__LINE__);

	// tjek at invalid dato bliver ignoreret som forventet
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['indflytning'] != '2007-07-01') fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('indflytning' => '2008-20-20'));
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['indflytning'] != '2007-07-01') fejl(__LINE__);

	// tjek valid dato
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['indflytning'] != '2007-07-01') fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('indflytning' => '2008-05-20'));
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['indflytning'] != '2008-05-20') fejl(__LINE__);

	// tjek invalid boolean
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['skjult_navn'] != false) fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('skjult_navn' => 'x'));
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['skjult_navn'] != false) fejl(__LINE__);

	// tjek valid boolean
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['skjult_navn'] != false) fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('skjult_navn' => true));
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['skjult_navn'] != true) fejl(__LINE__);

	// tjek invalid vaerelse
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['vaerelse'] != 'L88') fejl(__LINE__);
	try {
		backend_set_brugerdata('nyktte', array('vaerelse' => 'X99'));
	} catch(backend_database_exception $e) {
		if(!strstr("foreign key constraint", $e)) $test = true;
	}
	if(!$test) fejl(__LINE__);
	$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	if($r[0]['vaerelse'] != 'L88') fejl(__LINE__);

	teardown_testdata1();

	echo "ok\n";
}

function valider_bruger_og_password_test() {
	echo "Tester valider_bruger_og_password: ";
	setup_testdata1();

	//$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	//if($r[0]['password_cleartext'] == '') fejl(__LINE__);
	backend_set_brugerdata('nyktte', array('password' => 'testpass'));
	//$r = backend_dbquery("SELECT * FROM brugere WHERE brugernavn LIKE 'nyktte'");
	//if($r[0]['password_cleartext'] != 'testpass') fejl(__LINE__);

	if(backend_valider_bruger_og_password('nyktte', 'testpass') != true) fejl(__LINE__);
	if(backend_valider_bruger_og_password('nyktte', 'x') != false) fejl(__LINE__);

	teardown_testdata1();
	echo "ok\n";
}

function tilmeld_bruger_til_gruppe_test() {
	echo "Tester tilmeld_bruger_til_gruppe: ";
	setup_testdata1();

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte'");
	if(count($r) != 0) fejl(__LINE__);

	backend_set_gruppemedlemskab('nyktte', 'testgruppe');

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte'");
	if(count($r) != 1) fejl(__LINE__);

	teardown_testdata1();
	echo "ok\n";
}

function set_bruger_til_gruppeadmin_test() {
	echo "Tester set_bruger_til_gruppeadmin: ";
	setup_testdata1();

	backend_set_gruppemedlemskab('nyktte', 'testgruppe');
	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte' AND gruppenavn LIKE 'testgruppe'");
	if($r[0]['gruppeadmin'] == true) fejl(__LINE__);

	backend_set_gruppemedlemskab('nyktte', 'testgruppe',
		array('gruppeadmin' => true));

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte' AND gruppenavn LIKE 'testgruppe'");
	if($r[0]['gruppeadmin'] != true) fejl(__LINE__);

	teardown_testdata1();
	echo "ok\n";
}

function frameld_bruger_fra_gruppe_test() {
	echo "Tester frameld_bruger_fra_gruppe: ";
	setup_testdata1();

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte'");
	if(count($r) != 0) fejl(__LINE__);

	backend_set_gruppemedlemskab('nyktte', 'testgruppe');

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte'");
	if(count($r) != 1) fejl(__LINE__);

	backend_slet_gruppemedlemskab('nyktte', 'testgruppe');

	$r = backend_dbquery("SELECT * FROM gruppemedlemskaber "
		."WHERE brugernavn LIKE 'nyktte'");
	if(count($r) != 0) fejl(__LINE__);

	teardown_testdata1();
	echo "ok\n";
}

function hent_gruppe_medlemmer_test() {
	echo "Tester hent_gruppe_medlemmer: ";
	setup_testdata1();

	$r = backend_hent_gruppe_medlemmer('testgruppe');
	if(count($r) != 0) fejl(__LINE__);

	backend_set_gruppemedlemskab('nyktte', 'testgruppe');

	$r = backend_hent_gruppe_medlemmer('testgruppe');
	if(count($r) != 1) fejl(__LINE__);
	if($r[0]['brugernavn'] != 'nyktte') fejl(__LINE__);

	backend_slet_gruppemedlemskab('nyktte', 'testgruppe');

	teardown_testdata1();
	echo "ok\n";
}

function hent_brugers_medlemskaber_test() {
	echo "Tester hent_brugers_medlemskaber: ";
	setup_testdata1();

	$r = backend_hent_brugers_medlemskaber('nyktte');
	if(count($r) != 0) fejl(__LINE__);

	backend_set_gruppemedlemskab('nyktte', 'testgruppe');

	$r = backend_hent_brugers_medlemskaber('nyktte');
	if(count($r) != 1) fejl(__LINE__);
	if($r[0]['gruppenavn'] != 'testgruppe') fejl(__LINE__);

	backend_slet_gruppemedlemskab('nyktte', 'testgruppe');

	teardown_testdata1();
	echo "ok\n";
}

function hent_brugere_test() {
	echo "Tester hent_brugere: ";
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn2 Testefternavn2'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn3 Testefternavn3'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn4 Testefternavn4'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn5 Testefternavn5'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn6 Testefternavn6'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L03'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L04'");
	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L03', "
		."vaerelse_type='lejlighed'");
	backend_dbquery("INSERT INTO vaerelser SET vaerelse = 'L04', "
		."vaerelse_type='lejlighed'");
	backend_ny_bruger("L03", "Testfornavn Testefternavn", "2007-07-01", null, "1235", "lejer1");
	backend_ny_bruger("L03", "Testfornavn2 Testefternavn2", "2007-07-01", null, "1236", "lejer2");
	backend_ny_bruger("L04", "Testfornavn3 Testefternavn3", "2007-07-01", null, "1237", "lejer1");
	backend_ny_bruger("L04", "Testfornavn4 Testefternavn4", "2007-07-01", null, "1238", "lejer2");
	backend_ny_bruger("L04", "Testfornavn5 Testefternavn5", "2005-07-01", null, "1239", "lejer1");
	backend_ny_bruger("L03", "Testfornavn6 Testefternavn6", "2020-07-01", null, "1240", "lejer1");
        $r = backend_hent_brugere(array('brugernavn' => 'nyktte'));
	if(count($r) != 1) fejl(__LINE__);
        $r = backend_hent_brugere(array('navn' => 'Testfornavn'));
	if(count($r) != 5) fejl(__LINE__);
	$r = backend_hent_brugere(array('navn' => 'Testfornavn2'));
	if(count($r) != 1) fejl(__LINE__);
	$r = backend_hent_brugere(array('vaerelse' => 'L03'));
	if(count($r) != 2) fejl(__LINE__);
	$r = backend_hent_brugere(array('etage' => 1));
	if(count($r) != 2) fejl(__LINE__);
	if($r[0]["vaerelse"] != "L03") fejl(__LINE__);
	$r = backend_hent_brugere(array('etage' => 0));
	if(count($r) != 3) fejl(__LINE__);
	if($r[0]["vaerelse"] != "L04") fejl(__LINE__);
	$r = backend_hent_brugere(array('vaerelse_type' => 'lejlighed'));
	if(count($r) != 5) fejl(__LINE__);
	$r = backend_hent_brugere(array('vaerelse_type' => 'vaerelse'));
	if(count($r) != 0) fejl(__LINE__);
	$r = backend_hent_brugere(array('blok' => 'LM'));
	if(count($r) != 5) fejl(__LINE__);
	$r = backend_hent_brugere(array('blok' => 'JK'));
	if(count($r) != 0) fejl(__LINE__);
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn Testefternavn'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn2 Testefternavn2'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn3 Testefternavn3'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn4 Testefternavn4'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn5 Testefternavn5'");
	backend_dbquery("DELETE FROM brugere WHERE navn='Testfornavn6 Testefternavn6'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L03'");
	backend_dbquery("DELETE FROM vaerelser WHERE vaerelse = 'L04'");
	echo "ok\n";
}

function tilmeld_bruger_til_net_test() {
	echo 'Tester tilmeld_bruger_til_net: ';

	setup_testdata1();
	$r = backend_hent_brugere(array('brugernavn' => 'nyktte'));
	if($r[0]['net_tilmeldt_dato'] != null) fejl(__LINE__);
	backend_tilmeld_bruger_til_net('nyktte');
	$r = backend_hent_brugere(array('brugernavn' => 'nyktte'));
	if($r[0]['net_tilmeldt_dato'] == null) fejl(__LINE__);
	teardown_testdata1();

	echo "ok\n";
}

backend_dbquery("DELETE FROM brugere");
dbquery_test();
generer_passwd_test();
brugernavn_ledigt_test();
generer_brugernavn_test();
ny_bruger_test();
set_brugerdata_test();
valider_bruger_og_password_test();
tilmeld_bruger_til_gruppe_test();
set_bruger_til_gruppeadmin_test();
frameld_bruger_fra_gruppe_test();
hent_gruppe_medlemmer_test();
hent_brugers_medlemskaber_test();
hent_brugere_test();
tilmeld_bruger_til_net_test();

echo "Alle tests ok\n";
