<?php
require_once 'const.inc.php';
require_once 'helper.php';

$shortopts 	= '';
$longopts 	= array(
		'activate:'
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
	switch ($key)
	{
		case 'activate':
			$activate = $value;
			break;
		default:
			echo 'Zur Verfuegung stehen: activate';
			break;
	}
}

//echo "Wert für PHP Error:" . $activate . "\n";

if($activate == "true")
{
	// echo "Aktiviere PHP-Errors\n";
	$error_val = ini_get("error_reporting");
	error_reporting($error_val);
}
else
{
	// echo "Deaktiviere PHP-Errors\n";
	error_reporting(0);
}