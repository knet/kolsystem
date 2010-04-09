<?php
/**
 * Busy-waiter p� at dette er den eneste instans af dette CLI script. Afslutter
 * hvis der allerede er en anden instans der busy-waiter. Afslutter hvis ikke
 * kaldt som CLI script.
 */
function vent_indtil_eneste_instans() {
	if(!isset($_SERVER['argv']) || !$_SERVER['argv'])
		exit("Dette script m� ikke k�res direkte via webserver.");
	while(true) {
		// t�l antal k�rende instanser af dette script
		$koerende = 0;
		$r = explode("\n", shell_exec('ps -e -o cmd'));
		foreach($r as $linje) {
			if(strpos($linje, trim($_SERVER['argv'][0])) !== false) {
				$koerende++;
			}
		}

		// afslut hvis 2 eller flere allerede er k�rende (dvs. mindst 1 i k�)
		if($koerende > 2) exit();

		// loop videre indtil kun denne instans k�rer
		if($koerende == 2) sleep(2);
		else return;
	}
}

/**
 * Busy-waiter p� at der ikke k�rer en instans af en process der matcher givne
 * process s�geord.
 */
function vent_indtil_ingen_instans_af($process_soegeord) {
	while(true) {
		// t�l antal k�rende instanser af processen der matcher givne s�geord
		$koerende = 0;
		foreach(explode("\n", shell_exec('ps -e -o cmd')) as $linje)
			if(strpos($linje, $process_soegeord) !== false) $koerende++;

		// loop videre indtil der ikke k�rer en instans
		if($koerende > 0) sleep(2);
		else return;
	}
}

/**
 * Sikrer at scriptet k�rer som root
 */
function kun_root() {
	if(trim(shell_exec('whoami')) !== 'root')
		exit('Dette script skal k�res som root.');
}
