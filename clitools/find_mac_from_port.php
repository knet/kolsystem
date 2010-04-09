#! /usr/bin/php
<?php

include "../backend/network_management.php";

$port = $argv[1];
$switch = $argv[2];

if($port == "" || $switch == "") {
	echo "usage: find_mac_from_port.php port switch_ip_addr\n";
	exit();
}

$r = findMacFromPort($port,$switch);
if($r == null) echo "Did not find $mac in tables at switch $switch\n";
else print_r($r);
