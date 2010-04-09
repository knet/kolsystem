<?php
try {
	require_once('phpincludes/init.inc.php');
	auth_logout();	
}
catch(Exception $e) {
	fatal_error($e);	
}

header('location: index.php');
