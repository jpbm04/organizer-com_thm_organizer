<?php
/**
 * Die Datei erstellt eine XML-Datei mit den Pfaden zu allen Testsuites.
 *
 * Abgespeichert wird die XML-Datei unter /build/temp/testsuiteslist.xml . Der Wurzel-tag heißt <testsuites> und alle
 * Testsuiten sind von einem <testsuite> -tag umschlossen.
 *
 * @version 0.1.0
 */
require_once 'const.inc.php';
require_once 'helper.php';

$sortedextfile = JPATH_BASE . DS . 'build' . DS . 'temp' . DS . 'sortedextlist.xml';

if (!file_exists($sortedextfile)) {
    echo "ERROR: Datei mit der sortierten Liste aller Erweiterunge $sortedextfile konnte nicht gefunden werden." . PHP_EOL;
    exit(EXIT_FAILURE);
}

$sxml = simplexml_load_file($sortedextfile);
$allSortedExt = array();

$shortopts 	= '';
$longopts 	= array(
    'jversion:',
    'test:'
);

$options = getopt($shortopts, $longopts);

foreach ($options as $key => $value)
{
    switch ($key)
    {
        case 'jversion':
            $jversion = $value;
            break;
        case 'test':
            $testType = $value;
            break;
        default:
            echo 'Zur Verfuegung stehen: jversion, test';
            break;
    }
}

echo 'Verwendete Joomla-Version: ' . $jversion . "!";

foreach ($sxml->extension as $ext) {
    $allSortedExt[] = $ext->attributes()->xml;
}

if (count($allSortedExt) < 1) {
    echo 'ERROR: Es konnten keine Erweiterungen zum Installieren gefunden werden!' . PHP_EOL;
    exit(EXIT_FAILURE);
}
/*
 * Nach dem alle Erweiterungen installiert wurden werden deren Tests an die richtige Stelle zur Ausführung kopiert
 */
echo PHP_EOL . 'Test-Ordner aller Erweiterungen werden ermittelt und an die richtige stelle kopiert ...' . PHP_EOL;
foreach ($allSortedExt as $ext) {
    $src = FALSE;
    $extinfo = pathinfo($ext);
    $exml = simplexml_load_file($ext);

    if (file_exists($extinfo['dirname'] . DS . 'tests') && is_dir($extinfo['dirname'] . DS . 'tests')) {
        $src = $extinfo['dirname'] . DS . 'tests';
    } elseif (file_exists(substr($extinfo['dirname'], 0, strrpos($extinfo['dirname'], DS)) . DS . 'tests') && is_dir(substr($extinfo['dirname'], 0, strrpos($extinfo['dirname'], DS)) . DS . 'tests')) {
        $src = substr($extinfo['dirname'], 0, strrpos($extinfo['dirname'], DS)) . DS . 'tests';
    }

    if ($src) {
        $dest = JPATH_BASE . DS . 'tests' . DS . basename($extinfo['dirname']);
        copyRec($src, $dest);
    } else {
        echo "INFO: Fuer $exml->name konnte kein Test-Ordner ermittelt werden!" . PHP_EOL;
    }
}
echo 'OK! Ermittlung der Test-Ordner abgeschlossen.' . PHP_EOL;
echo PHP_EOL . 'Liste aller zur Verfuegung stehenden Testsuiten wird erstellt ...' . PHP_EOL;
$testSuitesXML = new SimpleXMLElement('<testsuitelist></testsuitelist>');
$testExtFolderArray = scandir(PATH_TESTS);

// Alle Ordner innerhalb von /tests werden durchlaufen, dies ist notwendig, weil 
// die PHPUnit Configs auch im Ordner admin oder sonst wo auftauchen koennten, 
// so dass deren Erweiterungszugehoerigkeit nicht richtig bestimmt werden koennte
foreach ($testExtFolderArray as $extName) {
    
    if (!is_dir(PATH_TESTS . DS . $extName) || $extName == '.' || $extName == '..') {
        continue;
    }
    
    // Alle PHPUnit Konfigurationen (phpunit.xml) finden
    $phpunitXMLs = getAllPHPUnitConfigXMLs(PATH_TESTS . DS . $extName, $testType);

    foreach ($phpunitXMLs as $phpunitXML) {
        $pathinfoPHPUnitXML = pathinfo($phpunitXML);
        $sxml = simplexml_load_file($phpunitXML);

        if (!empty($sxml->testsuites) && !empty($sxml->testsuites->testsuite)) {

            foreach ($sxml->testsuites->testsuite as $testsuite) {
                // hat falls gesetzt grundsaetzlich admin oder site als Wert
                $testSuiteName = '';

                if (!empty($sxml->testsuites->testsuite->attributes()->name)) {
                    $testSuiteName = (string) $sxml->testsuites->testsuite->attributes()->name;
                }

                // Die PHPUnit Konfiguration hat ein testsuite-Tag,
                // somit wird ein Eintrag fuer ein PHPUnit Aufruf fuer 
                // den Build-Prozess erstellt
                $phpunitNode = $testSuitesXML->addChild('phpunit');
                
                if (version_compare("3", $jversion) === 1)
                {
                	$phpunitNode->addAttribute('path', $pathinfoPHPUnitXML['dirname']);
                }
                else
                {
                	$phpunitNode->addAttribute('path', $pathinfoPHPUnitXML['dirname'] . DS . 'tests');
                }

                $phpunitNode->addAttribute('file', '');

                $configNode = $phpunitNode->addChild('config');
                $configNode->addAttribute('parameter', '--log-junit');
                $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'log-junit' . DS . $extName . '_' . $testSuiteName . '_junit.xml');

                $configNode = $phpunitNode->addChild('config');
                $configNode->addAttribute('parameter', '--coverage-clover');
                $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'coverage-clover' . DS . $extName . '_' . $testSuiteName . '_clover.xml');

                $configNode = $phpunitNode->addChild('config');
                $configNode->addAttribute('parameter', '--coverage-html');
                $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'coverage-html' . DS . $extName . '_' . $testSuiteName);

                // Hat die testsuite ein name-Attribut?
                if (!empty($testSuiteName)) {
                    $configNode = $phpunitNode->addChild('config');
                    $configNode->addAttribute('parameter', '--testsuite');
                    $configNode->addAttribute('argument', $testSuiteName);
                }

                if (version_compare("3", $jversion) === 1)
                {
                    $configNode = $phpunitNode->addChild('config');
                    $configNode->addAttribute('parameter', '--bootstrap');
                    $configNode->addAttribute('argument', PATH_TESTS . DS . 'bootstrap.php');
                }

                $configNode = $phpunitNode->addChild('config');
                $configNode->addAttribute('parameter', '--configuration');
                $configNode->addAttribute('argument', $phpunitXML);

                $configNode = $phpunitNode->addChild('config');
                $configNode->addAttribute('parameter', '--verbose');
                $configNode->addAttribute('argument', "");
            }
        }
        // Custom PHPUnit Konfigurationen mit der globalen verschmelzen, damit u.a. 
        // im Coverage nicht Joomla eigene Dateien auftauchen
        $resultMerge = mergePHPUnitConfigs($phpunitXML, JPATH_BASE . DS . 'build' . DS . 'config' . DS . 'phpunit' . DS . 'phpunit.xml');
        
        if (!$resultMerge) {
            echo "ERROR: Beim mergen der PHPUnit Konfiguration $phpunitXML mit der globalen Konfiguration ist ein Fehler aufgetreten!" . PHP_EOL;
            exit(EXIT_FAILURE);
        }
    }
    
    // Alle PHPUnit Testsuiten (testsuite.php) finden
    $testsuitesArray = findRecSuites(PATH_TESTS, $testType);
    
    foreach ($testsuitesArray as $testsuite) {
        
        $pathinfoTestsuite = pathinfo($testsuite);
        $phpunitNode = $testSuitesXML->addChild('phpunit');
        
        if (version_compare("3", $jversion) === 1)
        {
        	$phpunitNode->addAttribute('path', $pathinfoTestsuite['dirname']);
        }
        else
        {
        	$phpunitNode->addAttribute('path', $pathinfoTestsuite['dirname'] . DS . 'tests');
        }
        
        $phpunitNode->addAttribute('file', DS . $pathinfoTestsuite['basename']);

        $configNode = $phpunitNode->addChild('config');
        $configNode->addAttribute('parameter', '--log-junit');
        $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'log-junit' . DS . getCoverageHtmlName($testsuite) . '_junit.xml');

        $configNode = $phpunitNode->addChild('config');
        $configNode->addAttribute('parameter', '--coverage-clover');
        $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'coverage-clover' . DS . getCoverageHtmlName($testsuite) . '_clover.xml');

        $configNode = $phpunitNode->addChild('config');
        $configNode->addAttribute('parameter', '--coverage-html');
        $configNode->addAttribute('argument', PATH_REPORTS . DS . 'phpunit' . DS . 'coverage-html' . DS . getCoverageHtmlName($testsuite));

        if (version_compare("3", $jversion) === 1)
        {
            $configNode = $phpunitNode->addChild('config');
            $configNode->addAttribute('parameter', '--bootstrap');
            $configNode->addAttribute('argument', PATH_TESTS . DS . 'bootstrap.php');
        }

        $configNode = $phpunitNode->addChild('config');
        $configNode->addAttribute('parameter', '--configuration');
        $configNode->addAttribute('argument', JPATH_BASE . DS . 'build' . DS . 'config' . DS . 'phpunit' . DS . 'phpunit.xml');

        $configNode = $phpunitNode->addChild('config');
        $configNode->addAttribute('parameter', '--verbose');
        $configNode->addAttribute('argument', "");
    }
}

if (!$testSuitesXML->asXML(JPATH_BASE . DS . 'build' . DS . 'temp' . DS . 'testsuiteslist' . $testType . '.xml')) {
    echo 'Das Erstellen der Datei testsuitelist'  . $testType . '.xml ist fehlgeschlagen!' . PHP_EOL;
    exit(EXIT_FAILURE);
}
echo 'OK! Liste alle Testsuiten wurde erstellt.' . PHP_EOL;








/*
echo PHP_EOL . 'Liste aller zur Verfuegung stehenden Testsuiten wird erstellt ...' . PHP_EOL;
$testsuitesfile = JPATH_BASE . DS . 'build' . DS . 'temp' . DS . 'testsuiteslist.xml';
$testsuitesArray = findRecSuites(JPATH_BASE . DS . 'tests', 'unit');
$testsuitesXml = new SimpleXMLElement('<testsuites></testsuites>');

foreach ($testsuitesArray as $testsuite) {
    $testsuiteEntry = $testsuitesXml->addChild('testsuite');
    $testsuiteEntry->addChild('clovername', getCoverageHtmlName($testsuite) . '_clover.xml');
    $testsuiteEntry->addChild('coveragehtmlname', getCoverageHtmlName($testsuite));
    $testsuiteEntry->addChild('testsuitepath', $testsuite);
}

if (!$testsuitesXml->asXML($testsuitesfile)) {
    echo 'Das Erstellen der Datei testsuitelist.xml ist fehlgeschlagen!' . PHP_EOL;
    exit(EXIT_FAILURE);
}
echo 'OK! Liste alle Testsuiten wurde erstellt.' . PHP_EOL;


 * 
 */
