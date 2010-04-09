<?php
/**
 * Busy-waiter på at dette er den eneste instans af dette CLI script. Afslutter
 * hvis der allerede er en anden instans der busy-waiter. Afslutter hvis ikke
 * kaldt som CLI script.
 */
function vent_indtil_eneste_instans() {
	if(!isset($_SERVER['argv']) || !$_SERVER['argv'])
		exit("Dette script må ikke køres direkte via webserver.");
	while(true) {
		// tæl antal kørende instanser af dette script
		$koerende = 0;
		$r = explode("\n", shell_exec('ps -e -o cmd'));
		foreach($r as $linje) {
			if(strpos($linje, trim($_SERVER['argv'][0])) !== false) {
				$koerende++;
			}
		}

		// afslut hvis 2 eller flere allerede er kørende (dvs. mindst 1 i kø)
		if($koerende > 2) exit();

		// loop videre indtil kun denne instans kører
		if($koerende == 2) sleep(2);
		else return;
	}
}

/**
 * Busy-waiter på at der ikke kører en instans af en process der matcher givne
 * process søgeord.
 */
function vent_indtil_ingen_instans_af($process_soegeord) {
	while(true) {
		// tæl antal kørende instanser af processen der matcher givne søgeord
		$koerende = 0;
		foreach(explode("\n", shell_exec('ps -e -o cmd')) as $linje)
			if(strpos($linje, $process_soegeord) !== false) $koerende++;

		// loop videre indtil der ikke kører en instans
		if($koerende > 0) sleep(2);
		else return;
	}
}

/**
 * Sikrer at scriptet kører som root
 */
function kun_root() {
	if(trim(shell_exec('whoami')) !== 'root')
		exit('Dette script skal køres som root.');
}
