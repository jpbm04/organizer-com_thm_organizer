<?php
// Notwendige Dateien einbinden
require_once 'const.inc.php';
require_once 'helper.php';
require_once 'include_framework.php';

// Konstanten setzen
if (!defined('SORTED_EXT_XML')) define('SORTED_EXT_XML', join(DS, array(JPATH_BASE, 'build', 'temp', 'sortedextlist.xml')));
if (!defined('EXT_INSTALL_TEMP_FILE')) define('EXT_INSTALL_TEMP_FILE', join(DS, array(JPATH_BASE, 'build', 'temp', 'extinstalltempfile')));

JLoader::register('JYAML', join(DS, array(JPATH_LIBRARIES, 'jyaml', 'jyaml.php')));

// Notwendige Joomla! Bibliotheken einbinden
jimport('joomla.methods');
jimport('joomla.log.log');
jimport('joomla.application.application');
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');
jimport('joomla.database.database');
jimport('joomla.database.table');
jimport('joomla.environment.request');
jimport('joomla.filesystem.path');
jimport('joomla.filter.input');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.plugin.helper');

$shortopts 	= '';
$longopts 	= array(
    'user:',
    'password:'
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
    switch ($key)
    {
        case 'user':
            $user = $value;
            break;
        case 'password':
            $password = $value;
            break;
        default:
            echo 'Zur Verfuegung stehen: user, password';
            break;
    }
}

// Joomla! Konfigurationsobjekt erstellen
$config = JFactory::getConfig();
$config->set('debug', true);

// THM CAS disablen
$db = JFactory::getDBO();

$conditions = array(
    $db->qn('element') . ' LIKE '. $db->quote('%cas%')
    // $db->qn('type') . ' = '. $db->quote('plugin')
);
echo 'Suche nach CAS Erweiterungen in DUMP File!' . PHP_EOL;
$query = $db->getQuery(true);
$query->select('*');
$query->from($db->qn('#__extensions'));
$query->where($conditions);
$db->setQuery($query);
$casObjList = $db->loadObjectList();

foreach ($casObjList as $casObj) {
    $query = $db->getQuery(true);
    $query->update($db->qn('#__extensions'))->set($db->qn('enabled') . "= 0")->where($db->qn('extension_id') . "=" . $casObj->extension_id);
    $db->setQuery($query);
    $result = $db->execute();

    if($result){
        echo  $casObj->name . ' wurde deaktiviert!' . PHP_EOL;
    }
}
// AdminPW Salt
$salt = cryptPass($password);
// echo 'Admin PW Salt: ' . $salt . PHP_EOL;

// Add Admin User to MNI Dump
$profile = new stdClass();
$profile->id = 1;
$profile->name = 'Super User';
$profile->username = $user;
$profile->email = 'icampus@mni.thm.de';
$profile->password = $salt;
$profile->block = 0;
$profile->sendEmail = 1;
$profile->registerDate = date("Y-m-d H:i:s");
$profile->lastvisitDate = date("Y-m-d H:i:s");
$profile->activation = '';
$profile->params = '';
$profile->lastResetTime = '0000-00-00 00:00:00';
$profile->resetCount = 0;
$profile->otpKey = '';
$profile->otep = '';
$profile->requireReset = 0;
//$profile->usertype = 'Super Administrator';

$usergroup_mapping = new stdClass();
$usergroup_mapping->user_id = 1;
$usergroup_mapping->group_id = 8; // Super User

if($result)
{
    echo 'Administrator wurde in DB hinzugefügt.' . PHP_EOL;
}
else
{
	echo 'ERROR: Konnte Administrator nicht der DB hinzufügen.' . PHP_EOL;
	exit(EXIT_FAILURE);
}

// Get Admin-Group
$conditions = array(
    $db->qn('title') . ' = '. $db->quote('Administrator')
    // $db->qn('type') . ' = '. $db->quote('plugin')
);

$query = $db->getQuery(true);
$query->select('*');
$query->from($db->qn('#__usergroups'));
$query->where($conditions);
$db->setQuery($query);
$group = $db->loadObject();

// echo var_dump($group);

$map = new stdClass();
$map->user_id = $profile->id;
$map->group_id = $group->id;
$resultmap = $db->insertObject('#__user_usergroup_map', $map);

if($resultmap)
{
    echo 'Administrator wurde in Usergroup-Map hinzugefügt.' . PHP_EOL;
}
else
{
	echo 'ERROR: Konnte Administrator nicht der Usergroup-Map-Tabelle hinzufügen.' . PHP_EOL;
	exit(EXIT_FAILURE);
}

// Load User
if(JFactory::getUser()->id == null || JFactory::getUser()->id == "0"){

    echo 'Es wurde kein eingeloggter Benutzer gefunden!' . PHP_EOL;

    // speichere den Benutzernamen in einem Array
    $data['username'] = $user;
    // speichere das Passwort in einem Array
    $data['password'] = $password;

    // Setze true/false ob sich Joomla! an den Benutzer "erinnern" soll
    $option['remember'] = true;
    // Setze true/false ob bei Fehlgeschlagenem Login eine Warnung ausgegeben werden soll
    $option['silent'] = true;

    // Logge den Benutzer ein
    // echo 'Versuche login mit Benutzer: '. $user . '!' . PHP_EOL;
    // echo 'Versuche login mit Password: '. $password . '!' . PHP_EOL;

    $mainframe->login($data, $option);

	echo 'Nun eingeloggt als Benutzer: ' . JFactory::getUser()->username;
}

// Load Library language
$lang = JFactory::getLanguage();
$lang->load('lib_joomla', JPATH_ADMINISTRATOR, $lang->getDefault(), true);

if (!file_exists(SORTED_EXT_XML)) {
	echo 'ERROR: Datei mit der sortierten Liste aller Erweiterungen ' . SORTED_EXT_XML . ' konnte nicht gefunden werden!' . PHP_EOL;
	exit(EXIT_FAILURE);
}

$sxml 			= simplexml_load_file(SORTED_EXT_XML);
$allSortedExt 	= array();

foreach ($sxml->extension as $ext) {
	$allSortedExt[] = $ext->attributes()->xml;
}

if (count($allSortedExt) < 1) {
	echo 'ERROR: Es konnten keine Erweiterungen zum Installieren bzw. Aktualisieren gefunden werden!' . PHP_EOL;
	exit(EXIT_FAILURE);
}

// Erstellen einer Temp-Datei, falls Joomla! die Skriptausfuehrung vorzeitig beendet
$extInsTemFil = fopen(EXT_INSTALL_TEMP_FILE, 'w');
fclose($extInsTemFil);

// Alle bisher installierten Erweiterungen ermitteln
$lang->load('com_installer', JPATH_ADMINISTRATOR, $lang->getDefault(), true);

$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select('*');
$query->from($db->qn('#__extensions'));
$db->setQuery($query);
$extObjList = $db->loadObjectList();
$db = JFactory::getDbo();

// Durchlaufen aller Erweiterungen, die waehrend der Jobausfuehrung getestet werden sollen
foreach ($allSortedExt as $ext) {
	$exml = simplexml_load_file($ext);
	echo PHP_EOL . 'Erweiterung ' . $exml->name . ' wird in der Datenbank gesucht ...' . PHP_EOL;

	$extinfo 		= pathinfo($ext);
	$extElement 	= getElementByManifest($ext);
	$isExtInstalled = FALSE;

	// Das element der Erweiterung $ext wird in der Datenbank gesucht
	foreach ($extObjList as $extObj) {
		// type und element muessen übereinstimmen, damit gilt die Erweiterung als installiert
		if ($extObj->type === (string) $exml->attributes()->type && $extObj->element === $extElement) {
			$isExtInstalled = TRUE;
			break;
		}
	}

	// Get an installer instance
	$installer = new JInstaller;

	if (!$isExtInstalled) {
		echo 'Beginn der Installation der Erweiterung ...' . PHP_EOL;

		if (!$installer->install($extinfo['dirname'])) {
			echo 'ERROR: Installation ist fehlgeschlagen!' . PHP_EOL;
			echo "Extension information:" . PHP_EOL;
			var_dump($extinfo);
			echo "PHP error information:" . PHP_EOL;
			var_dump(error_get_last());
			echo "Database error information:" . PHP_EOL;
			var_dump($db->getErrorNum());
			var_dump($db->getErrorMsg());
			var_dump($db->stderr(true));
			$errors = JError::getErrors();
			$errors = JError::getErrors();
			foreach ($errors as $error) {
				echo $error;
			}
			exit(EXIT_FAILURE);
		} else 	{
			echo 'OK! Die Installation war erfolgreich.' . PHP_EOL;
		}
	} else {
		echo 'Beginn der Aktualisierung der Erweiterung ...' . PHP_EOL;
		
		if (!$installer->update($extinfo['dirname'])) {
			echo 'ERROR: Aktualisierung ist fehlgeschlagen!' . PHP_EOL;
			echo "Extension information:" . PHP_EOL;
			var_dump($extinfo);
			echo "PHP error information:" . PHP_EOL;
			var_dump(error_get_last());
			echo "Database error information:" . PHP_EOL;
			var_dump($db->getErrorNum());
			var_dump($db->getErrorMsg());
			var_dump($db->stderr(true));
			$errors = JError::getErrors();
			$errors = JError::getErrors();
			foreach ($errors as $error) {
				echo $error;
			}
			exit(EXIT_FAILURE);
		} else 	{
			echo 'OK! Die Aktualisierung war erfolgreich.' . PHP_EOL;
		}
	}
}

// Aktiviere PHP Fehler
$error_val = ini_get("error_reporting");
error_reporting($error_val);

unlink(JPATH_BASE.DS.'build'.DS.'temp'.DS.'extinstalltempfile');
