#!/usr/bin/php
<?php
include "../backend/network_management.inc.php";
require_once '../backend/backend.inc.php';

if($argv[1] == "") {
	echo "usage: device_locate.php taraget_mac_or_ip\n";
	exit();
}

if(!ereg(":", $argv[1])) {
	echo "assuming input was an IP\n";
	$targetMac = ipToMac($argv[1]);
	if($targetMac == null) {
		echo "could not find mac address\n";
		exit();
	}
	echo $argv[1]." has mac address $targetMac\n";
}
else $targetMac = $argv[1];

$cachefile = ".device_locate.cache";
if(file_exists($cachefile) && time()-filemtime($cachefile) < 1800) {
	echo "uplink index cache is younger than 30 minutes. Using cache.\n";
	include $cachefile;
} else {

	// find all switch mac addresses
	$switchMacs = findSwitchMacAddresses();

	// in all switches, find all ports which leads to other ports
	// first initialize datastruct
	foreach($switchIps as $switchName => $switchIp) {
		echo "detecting uplink ports on $switchName\n";
		$uplinks[$switchName] = array();
		foreach($switchMacs as $switchMac) {
			$port = findPortFromMac($switchMac, $switchIp);
			if($port != null) {
				if(!in_array($port, $uplinks[$switchName])) {
					$uplinks[$switchName][] = $port;
				}
			}
		}
	}

	$cacheout = "<?php\n";
	$cacheout .= "\$switchMacs=".var_export($switchMacs,true).";";
	$cacheout .= "\$uplinks=".var_export($uplinks,true).";";
	file_put_contents($cachefile, $cacheout);
}

echo "now searching for $targetMac on switches\n";
foreach($switchIps as $switchName => $switchIp) {
	$port = findPortFromMac($targetMac, $switchIp);
	if($port != null && !in_array($port, $uplinks[$switchName])) {
		echo "$targetMac seems to be plugged in port $port ".
		"on switch '$switchName' (${switchIps[$switchName]}), ";
		$r = findRoomFromDb($switchIps[$switchName], $port);
		if($r) echo "in database this is room '$r'\n";
		else echo "room not found in database\n";
		$found = true;
	}
}
if(!$found) echo "did not find any ports in any switches ".
	"that matched and were not uplink ports\n";


function findRoomFromDb($switchip, $port) {
	$db=mysql_connect("mysql.nybro.dk","web","nybronet");
	mysql_select_db("nybronet",$db);
	$r=mysql_query("SELECT værelse FROM statisk ".
		"WHERE switch_ip='$switchip' AND switch_port='$port'", $db);
	$rr = mysql_fetch_array($r);
	return $rr[0];
}
