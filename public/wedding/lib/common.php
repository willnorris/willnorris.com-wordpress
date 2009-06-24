<?php

//require_once 'MDB2.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/Invitation.php';
require_once dirname(__FILE__) . '/Guest.php';
require_once dirname(__FILE__) . '/messages.php';
require_once dirname(__FILE__) . '/pre.php';
require_once dirname(__FILE__) . '/local.php';

/*
$db =& MDB2::factory($db_dsn);
if (PEAR::isError($db)) {
	die($db->getMessage());
}
 */


$alertEmail = 'wedding@willnorris.com';

?>
