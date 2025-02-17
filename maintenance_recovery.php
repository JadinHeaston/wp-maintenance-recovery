<?php

define('DEFAULT_SECONDS_ELAPSED', 300);

//Parsing options.
$options = getopt(
	'',
	[
		'maintenance-file:', //Path to maintenance file. 
		'seconds-elapsed::', //Defaults to 
	]
);

if (count($options) > 0 === false || isset($options['maintenance-file']) === false || is_string($options['maintenance-file']) === false || $options['maintenance-file'] === '')
	exit('No valid `--maintenance-file` provided. Provide the full path to the WordPress maintenance file location. (Often `.maintenance` in the root WP directory)');

if (isset($options['seconds-elapsed']))
	$secondsElapsedThreshold = intval($options['seconds-elapsed']);
else
	$secondsElapsedThreshold = DEFAULT_SECONDS_ELAPSED;

echo 'Seconds Elapsed Threshold: ' . $secondsElapsedThreshold  . PHP_EOL;

if (file_exists($options['maintenance-file']) === false)
	exit('File does not exist.');

$lastModifiedTime = filectime($options['maintenance-file']);
if ($lastModifiedTime === false)
	exit('Failed to get last modified time of file.');

$currentTimestamp = time();
$timeDifference = ($currentTimestamp - $lastModifiedTime);

echo 'Time Difference: (' . $timeDifference . ')';

if ($timeDifference > $secondsElapsedThreshold)
{
	echo 'Attempting to delete file.' . PHP_EOL;
	if (unlink($options['maintenance-file']) === true)
		exit('Deleted maintenance file.');
	else
		exit('FAILED TO DELETE MAINTENANCE FILE.');
}

exit(0);
