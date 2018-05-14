<?php
require_once 'const.inc.php';
require_once 'helper.php';

// Uebergebene Skriptparameter auslesen
$shortopts 	= '';
$longopts 	= array('jdbname');
$options 	= getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
	switch ($key)
	{
		case 'jdbname':
			echo getDBName();
			break;
		default:
			echo 'Zur Verfuegung stehen: jdbname';
			break;
	}
}
?>