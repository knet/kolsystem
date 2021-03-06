<?php
/* Generel setup */
define("DORM_NAME", "Test Kollegiet");
define("DORM_WEBSITE", "http://www.testkollegie.dk/");
define("DORM_DOMAIN", "testkollegie.dk");

/* Database */
define('KOLSYSTEM_DB_HOST', 'localhost');
define('KOLSYSTEM_DB_NAME', 'kolsystem');
define('KOLSYSTEM_DB_USERNAME', 'kolsystem');
define('KOLSYSTEM_DB_PASSWORD', '');

/* Lokale unix filer */
define('UNIXFILE_PASSWD', '/etc/passwd');
define('UNIXFILE_SHADOW', '/etc/shadow');
define('UNIXFILE_GROUP', '/etc/group');
define('UNIXFILE_ALIASES', '/etc/aliases');
define('SMBPASSWD', '/etc/samba/smbpasswd');

/* R-sync til knet */
define('RSYNC_PRIVKEY', '/kolsystem/knet_rsync_priv_key');
define('RSYNC_DIR', '/kolsystem/data/knet_rsync/');
define('RSYNC_HOST', 'update2.k-net.dk');

/* KABAS-relateret */
define('KABAS_ENABLE', false);
define('KABASIMPORT_LOGFILE', '/nyksystem/log/kabas_import_batch.log');
define('KABAS_DATAFILE_NEWEST', '/nyksystem/data/kabas_web_nyk_newest.xml');
define('KABAS_DATAFILE_PATH', 'ftp://x:x@194.239.0.110/web_nyk.xml');
define('KABAS_UDTRAEK_SCHEMA', '/nyksystem/batches/kabas_udtraek_schema.xsd');

/* Webfrontend */
define('WEBFRONTEND_PATH','/kolsystem/webfrontend/');
define('WEBFRONTEND_LOGFILE','/kolsystem/log/webfrontend.log');
define('WEBFRONTEND_TITLE', 'Dorm name');

/* Batches */
define('BATCHES_PATH','/kolsystem/batches/');
define('BRUGEROPRYDNINGBATCH_LOGFILE', '/kolsystem/log/bruger_oprydning_batch.log');

/* Andet */
define('MINIMALIST_DIR', '/var/spool/minimalist/');
define('DEFAULT_GID', '100'); // nye beboeres primaere gruppe id

