<?php

require_once 'MDB2.php';
require_once 'functions.php';
require_once 'Invitation.php';
require_once 'Guest.php';
require_once 'messages.php';
require_once 'pre.php';
require_once 'local.php';

$db =& MDB2::factory($db_dsn);
if (PEAR::isError($db)) {
	die($db->getMessage());
}



?>
