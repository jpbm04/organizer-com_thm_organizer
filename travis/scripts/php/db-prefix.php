<?php
require_once 'const.inc.php';
require_once JPATH_BASE . DS . 'configuration.php';

// Tabellen Prefix im mni.sql dump setzen
$shortopts = '';
$longopts = array(
	'jdbdump:',
	'jdbprefix:',
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
	switch ($key)
	{
		case 'jdbdump':
			$jdbdump = $value;
			break;
		case 'jdbprefix':
			$jdbprefix = $value;
			break;
		default:
			echo 'ERROR: Zur Verfuegung stehen: jdbprefix !' . PHP_EOL;
			exit(EXIT_FAILURE);
			break;
	}
}

$jconfig = new JConfig;

// Tabellen Prefix neu setzen
if (file_exists($jdbdump)) {
	file_put_contents($jdbdump, str_replace($jconfig->dbprefix, $jdbprefix, file_get_contents($jdbdump)));
}