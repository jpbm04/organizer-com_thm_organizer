<?php
/**
 * Das Skript loescht die Ordner der Erweiterungen, welche aus der Ueberwachung von Jenkins entfernt wurden.
 *
 * Alle Erweiterungen landen fuer gewoehnlich in /build/temp/extensions deren Inhalt wird in /extensions kopiert
 * und aus /build/temp/extensions wieder geloescht, bis auf den .git Ordner. Dieser Ordner bleibt fuer jede
 * Erweiterung in /build/temp/extensions/xxx damit Jenkins weiss, welche Version er bereits gepullt hat.
 * Ueber die Jenkins Oberflaeche lassen sich beliebig Erweiterungen hinzufuegen und wieder rausnehmen. Beim
 * herausnehmen, bleibt jedoch der Ordner in /build/temp/extensions/xxx/.git erhalten. Somit wuerde eine leere
 * Erweiterungen waehrend des Build-Prozesses kopiert werden, diese Leichen werden mittels diesem Skript aus
 * /extensions geloescht um beim archivieren nicht beruecksichtigt zu werden.
 */
require_once 'const.inc.php';
require_once 'helper.php';

$deleteArray 	= array();

// durchlaufen des /extensions Ordners
foreach (scandir(PATH_EXTENSIONS) as $extDir)
{
	// falls ein Punkt oder Doppelpunkt auftaucht wird dieser uebersprungen
	if ($extDir == '.' || $extDir == '..')
	{
		continue;
	}

	// wird ein Ordner gefunden wird sein Pfad als Schlüssel in das $deleteArray eingetragen
	// mit dem Wert true, also zum loeschen vorgesehen
	$deleteArray[PATH_EXTENSIONS.DS.$extDir] = true;

	// der Inhalt des gefunden Ordners wird gescannt, befinden sich darin noch andere Dateien
	// und Ordner ausser .git oder .gitignore wird der Pfad im $deleteArray auf false gesetzt
	foreach (scandir(PATH_EXTENSIONS.DS.$extDir) as $extItem)
	{
		if ($extItem == '.' || $extItem == '..' || $extItem == '.git' || $extItem == '.gitignore')
		{
			continue;
		}

		$deleteArray[PATH_EXTENSIONS.DS.$extDir] = false;
	}
}

foreach ($deleteArray as $extPath => $delPro)
{
	if ($delPro && is_dir($extPath))
	{
		if (rrmdir($extPath))
		{
			echo "Folder from path $extPath was successfully deleted." . PHP_EOL;
		}
		else
		{
			echo "ERROR: Folder from path $extPath could not be deleted!" . PHP_EOL;
			exit(EXIT_FAILURE);
		}
	}
}
?>