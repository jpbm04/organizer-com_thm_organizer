<?php
/**
 * This file contains checks to validate that the tables prefix is '#__'.
 */

require_once 'const.inc.php';

$path = realpath(PATH_EXTENSIONS);

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

$invalidPrefixFound = 0;

foreach($objects as $name => $object){
	$fileInfo = pathinfo($name);

	if (isset($fileInfo["extension"]) && $fileInfo["extension"] === "sql" && isset($fileInfo["dirname"]) && strpos($fileInfo["dirname"], 'unit-tests') !== false && strpos($fileInfo["dirname"], 'gui-tests') !== false)
	{
		$results = array();
		$fileContent = file_get_contents($name);
		$results = array_merge($results, checkCREATE($fileContent));
		$results = array_merge($results, checkDROP($fileContent));
		$results = array_merge($results, checkALTER($fileContent));
		$results = array_merge($results, checkINSERT($fileContent));
		$results = array_merge($results, checkUPDATE($fileContent));
		$results = array_merge($results, checkREFERENCES($fileContent));

		if (count($results) > 0)
		{
			$invalidPrefixFound = 1;
			sendOutput($name, $results);
		}
	}
}

exit($invalidPrefixFound);

/*
 * Checks table prefix for CREATE statements.
 */
function checkCREATE($sql)
{
	$result = array();
	$muster = "/CREATE (TEMPORARY )?TABLE\s+(IF NOT EXISTS )?(.*?)\(/is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[3];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		if (strpos($tableMatches[$index][0], "#__") === false)
		{
			$result[] = $matches[0][$index];
		}
	}
	return $result;
}

/*
 * Checks table prefix for DROP statements.
 */
function checkDROP($sql)
{
	$result = array();
	$muster = "/DROP (TEMPORARY )?TABLE\s+(IF EXISTS )?(.*?)(RESTRICT|CASCADE|;)/is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[3];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		$tables = explode(",", $tableMatches[$index][0]);
		for ($tableIndex = 0; $tableIndex < count($tables); $tableIndex++)
		{
			if (strpos($tables[$tableIndex], "#__") === false)
			{
				$result[] =  $matches[0][$index];
			}
		}
	}
	return $result;
}

/*
 * Checks table prefix for ALTER statements.
 */
function checkALTER($sql)
{
	$result = array();
	$muster = "/ALTER (IGNORE )?TABLE\s+(.*?) /is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[2];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		if (strpos($tableMatches[$index][0], "#__") === false)
		{
			$result[] = $matches[0][$index];
		}
	}
	return $result;
}

/*
 * Checks table prefix for INSERT statements.
 */
function checkINSERT($sql)
{
	$result = array();
	$muster = "/INSERT (LOW_PRIORITY |DELAYED |HIGH_PRIORITY )?(IGNORE )?(INTO )?(.*?) /is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[4];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		if (strpos($tableMatches[$index][0], "#__") === false)
		{
			$result[] = $matches[0][$index];
		}
	}
	return $result;
}

/*
 * Checks table prefix for UPDATE statements.
 */
function checkUPDATE($sql)
{
	$result = array();
	$muster = "/(^|\n|;)UPDATE (LOW_PRIORITY )?(IGNORE )?(.*?) /is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[4];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		if (strpos($tableMatches[$index][0], "#__") === false)
		{
			$result[] = $matches[0][$index];
		}
	}
	return $result;
}

/*
 * Checks table prefix for REFERENCES statements.
 */
function checkREFERENCES($sql)
{
	$result = array();
	$muster = "/REFERENCES (.*?) \(?/is";
	preg_match_all($muster, $sql, $matches, PREG_OFFSET_CAPTURE);
	$tableMatches = $matches[1];
	for ($index = 0; $index < count($tableMatches); $index++)
	{
		if (strpos($tableMatches[$index][0], "#__") === false)
		{
			$result[] = $matches[0][$index];
		}
	}
	return $result;
}

/*
 * Displays the path to a file with invalid table prefix.
 */
function sendOutput($filename, $results)
{
	$filename = str_replace ( PATH_EXTENSIONS , "" , $filename);
	echo "Invalid table prefix found in file " . $filename . ":"  . PHP_EOL;

	for ($index = 0; $index < count($results); $index++)
	{
		echo "Position " . $results[$index][1]. " near \"" . $results[$index][0] . "\"" . PHP_EOL;
	}
	echo PHP_EOL;
}