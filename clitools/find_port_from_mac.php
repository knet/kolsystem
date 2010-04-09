#! /usr/bin/php
<?php

include "../backend/network_management.inc.php";

$mac = $argv[1];
$switch = $argv[2];

if($mac == "" || $switch == "") {
	echo "usage: find_port_from_mac.php device_mac_addr switch_ip_addr\n";
	exit();
}

$r = findPortFromMac($mac,$switch);
if($r == null) echo "Did not find $mac in tables at switch $switch\n";
else echo "Found $mac on port $r in switch $switch\n";
