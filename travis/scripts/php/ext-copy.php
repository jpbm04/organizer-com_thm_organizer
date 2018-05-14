<?php
require_once 'const.inc.php';
require_once 'helper.php';

$extInstallCollection = JPATH_BASE . DS . 'build' . DS . 'config' . DS . 'ext-install.xml.dist';

$shortopts 	= '';
$longopts 	= array(
		'extinstall:',
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
	switch ($key)
	{
		case 'extinstall':
			$extInstallCollection = $value;
			break;
		default:
			echo 'Zur Verfuegung stehen: extinstall';
			break;
	}
}

$exml = simplexml_load_file($extInstallCollection);
$dest = PATH_EXTENSIONS;

foreach ($exml->extension as $ext)
{
	if (!empty($ext->path))
	{
		copyRec($ext->path, $dest . DS . basename($ext->path));
	}
}
?>