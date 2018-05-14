<?php
/**
 * Das Skript ueberprueft fuer jede Erweiterung im Ordner extensions ob deren Elementname, Ordnername
 * und Dateiname uebereinstimmen.
 */
require_once 'const.inc.php';
require_once 'helper.php';

$manifestFiles 	= findRecManifestFiles(PATH_EXTENSIONS);

foreach ($manifestFiles as $manifest)
{
	$path_parts = pathinfo($manifest);
	$filename 	= $path_parts['filename'];
	$dirname 	= basename($path_parts['dirname']);
	$element 	= getElementByManifest($manifest);

	if ($filename == $dirname && $filename == $element)
	{
		echo "OK! Bei '$filename' stimmen Ordner-, Datei- und Elementname ueberein." . PHP_EOL;
	}
	else
	{
		echo PHP_EOL . 'WARNING! Ordnername, Dateiname und Element sollten uebereinstimmen!' . PHP_EOL;
		echo 'Ordnername: ' . $dirname . PHP_EOL;
		echo 'Dateiname:  ' . $path_parts['basename'] . PHP_EOL;
		echo 'Element:    ' . $element . PHP_EOL . PHP_EOL;
	}
}
?>