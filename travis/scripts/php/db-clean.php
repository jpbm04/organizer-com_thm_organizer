<?php
require_once 'const.inc.php';
require_once 'helper.php';

$file 		= PATH_SQL_FILES . DS . 'db-clean.sql';
$shortopts 	= '';
$longopts 	= array(
	'dbhost:',
	'dbport:',
	'dbuser:',
	'dbpass:',
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
	switch ($key)
	{
		case 'dbhost':
			$dbhost = $value;
			break;
		case 'dbport':
			$dbport = $value;
			break;
		case 'dbuser':
			$dbuser = $value;
			break;
		case 'dbpass':
			$dbpass = $value;
			break;
		default:
			echo 'Zur Verfuegung stehen: dbhost, dbport, dbuser, dbpass';
			break;
	}
}

if (empty($dbhost) || empty($dbport) || empty($dbuser))
{
	echo 'ERROR: Es wurden nicht alle notwendingen Zugangsdaten fuer die Datenbank angegeben!' . PHP_EOL;
	exit(EXIT_FAILURE);
}

if (empty($dbpass))
{
	echo 'INFO: Das Passwort fuer die Datenbank scheint leer zu sein o.O' . PHP_EOL;
	$dbpass = '';
}

//list($user, $pass, $host, $file) 	= checkCommandLineArguments(parseCommandLineArguments($_SERVER['argv']), 'clean');
list($jdbname, $jdbuser) = readCurrentDatabaseInfo();

if (!empty($jdbname) && !empty($jdbuser))
{
	if (isTestDatabase($jdbname))
	{
		removeTestDatabase($dbhost, $dbport, $dbuser, $dbpass, $jdbname, $jdbuser, $file);
	}
}
?>