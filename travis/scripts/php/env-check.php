<?php
require_once 'const.inc.php';

function collectError($message = '')
{
	static $errors = array();

	if ($message)
	{
		$errors[] = $message . PHP_EOL;
	}
	else
	{
		if(!empty($errors))
		{
			print_r($errors);
			exit(EXIT_FAILURE);
		}
	}
}

function checkExtensions(array $extensions)
{
	foreach ($extensions as $name)
	{
		if (!extension_loaded($name))
		{
			collectError("Joomla! requires $name extension to be installed.");
		}
	}
}

function checkFunctions(array $functions)
{
	foreach ($functions as $name)
	{
		if (!function_exists($name))
		{
			collectError("Joomla! requires $name function to be installed.");
		}
	}
}

// PHP Version
$requiredVersion = '5.2.4';
if (version_compare(PHP_VERSION, $requiredVersion, '<'))
{
	collectError('Joomla! requires PHP version ' . $requiredVersion . '. Installed: ' . PHP_VERSION);
}

// php.ini
if (ini_get('register_globals') == 1)
{
	collectError("Joomla! requires 'register_globals' set to 'Off' in your php.ini");
}

// Extensions
$requiredExtensions = array(
	'xsl', 'curl', 'mysqli', 'zlib', 'xml', 'mbstring', 'zip' //, "soap", "ldap", "openssl", "bcmath", "gettext", "pspell", "libxml", "xmlrpc"
);
checkExtensions($requiredExtensions);

// Functions
$requiredFunctions = array(
	'json_encode', 'json_decode', 'zip_open', 'zip_read'
);
checkFunctions($requiredFunctions);

collectError();