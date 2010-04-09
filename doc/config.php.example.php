<?php

/* Database */
define('NYKSYSTEM_DB_HOST', 'localhost');
define('NYKSYSTEM_DB_NAME', 'nyksystem');
define('NYKSYSTEM_DB_USERNAME', 'nyksystem');
define('NYKSYSTEM_DB_PASSWORD', '');

/* Lokale unix filer */
define('UNIXFILE_PASSWD', '/etc/passwd');
define('UNIXFILE_SHADOW', '/etc/shadow');
define('UNIXFILE_GROUP', '/etc/group');
define('UNIXFILE_ALIASES', '/etc/aliases');
define('SMBPASSWD', '/etc/samba/smbpasswd');

/* R-sync til knet */
define('RSYNC_PRIVKEY', '/nyksystem/knet_rsync_priv_key');
define('RSYNC_DIR', '/nyksystem/data/knet_rsync/');
define('RSYNC_HOST', 'update2.k-net.dk');


/* KABAS-relateret */
define('KABASIMPORT_LOGFILE', '/nyksystem/log/kabas_import_batch.log');
define('KABAS_DATAFILE_NEWEST', '/nyksystem/data/kabas_web_nyk_newest.xml');
define('KABAS_DATAFILE_PATH', 'ftp://x:x@194.239.0.110/web_nyk.xml');
define('KABAS_UDTRAEK_SCHEMA', '/nyksystem/batches/kabas_udtraek_schema.xsd');

/* Webfrontend */
define('WEBFRONTEND_PATH','/nyksystem/webfrontend/');
define('WEBFRONTEND_LOGFILE','/nyksystem/log/webfrontend.log');

/* Andet */
define('MINIMALIST_DIR', '/var/spool/minimalist/');
define('DEFAULT_GID', '100'); // nye beboeres primaere gruppe id

