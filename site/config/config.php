<?php

// config générale du site Kirby

$options = [
	'debug' => false,
	'yaml.handler' => 'symfony',
];

$local = __DIR__ . '/config.local.php';
if (file_exists($local)) {
	$options = array_replace_recursive($options, require $local);
}

return $options;
