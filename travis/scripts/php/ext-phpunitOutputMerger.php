<?php
require_once 'const.inc.php';
require_once 'helper.php';

// Zusammenfuegen aller von PHPUnit erstellten JUnit-Log-Dateien.
$dir 					= JPATH_BASE . DS . 'build' . DS . 'reports' . DS . 'phpunit' . DS . 'log-junit';
$dom 					= getJUnitTestSuiteTemplate();
$junitTestSuiteFiles 	= getAllFilesWithSuffix($dir, '_junit.xml');

foreach ($junitTestSuiteFiles as $junitTestSuiteFile)
{
	$doc = new DOMDocument();
	$doc->load($junitTestSuiteFile);
	$dom = mergeTestSuites($dom, $doc);
}

$dom->save(JPATH_BASE . DS . 'build' . DS . 'reports' . DS . 'phpunit' . DS . 'phpunit_log_junit.xml');

// Zusammenfuegen aller von PHPUnit erstellten Clover-Dateien.
$dir 			= JPATH_BASE . DS . 'build' . DS . 'reports' . DS . 'phpunit' . DS . 'coverage-clover';
$dom 			= getPhpUnitCloverTemplate();
$cloverFiles 	= getAllFilesWithSuffix($dir, '_clover.xml');

foreach ($cloverFiles as $cloverFile)
{
	$doc = new DOMDocument();
	$doc->load($cloverFile);
	$dom = mergePhpUnitClover($dom, $doc);
}

$dom->save(JPATH_BASE . DS . 'build' . DS . 'reports' . DS . 'phpunit' . DS . 'phpunit_coverage_clover.xml');
?>