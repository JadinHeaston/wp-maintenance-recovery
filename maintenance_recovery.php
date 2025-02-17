<?php

define('DEFAULT_SECONDS_ELAPSED', 300);
define('MAIL_ENABLED', false);
define('MAIL_SENDER', 'test@example.com');
define('MAIL_RECIPIENTS', []);
define('MAIL_TEMPLATES', [
	'successful_deletion' => [
		'subject' => <<<HTML
			MAINTENANCE RECOVERY - SUCCESS
			HTML,
		'message' => <<<HTML
			The maintenance file was successfully deleted for the following path.
			<br>
			Please verify that the site is fully operational by visiting the site.
			<br>
			<br>
			Ensure plugins get manually updated if this repeats.
			HTML
	],
	'failed_deletion' => [
		'subject' => <<<HTML
			MAINTENANCE RECOVERY - FAILED
			HTML,
		'message' => <<<HTML
			The maintenance file was UNSUCESSFULLY deleted for the following path.
			<br>
			Please verify that the site is fully operational by visiting the site.
			<br>
			<br>
			The site may be stuck in maintenance mode. If so, delete the file manually.
			<br>
			<br>
			Ensure plugins get updated manually.
			HTML
	]
]);
define(
	'MAIL_HEADERS',
	[
		'Content-type' => 'text/html',
		'From' => MAIL_SENDER,
		'MIME-Version' => '1.0',
	]
);


//Parsing options.
$options = getopt(
	'',
	[
		'maintenance-file:', //Path to maintenance file. 
		'seconds-elapsed::',
		'site-url::', //Used for emails to provide an easy link to check the site in question.
	]
);

if (count($options) > 0 === false || isset($options['maintenance-file']) === false || is_string($options['maintenance-file']) === false || $options['maintenance-file'] === '')
	exit('No valid `--maintenance-file` provided. Provide the full path to the WordPress maintenance file location. (Often `.maintenance` in the root WP directory)');

//Updating mail template with provided argument information.
if (mailEnabled() === true)
{
	foreach (MAIL_TEMPLATES as $mailTemplate)
	{
		if (isset($options['site-url']) && $options['site-url'] !== '')
			$siteURL = 'Site URL: <a href="' . urlencode($options['site-url']) . '">' . $options['site-url'] . '</a>';

		$serverInformation = php_uname();

		$mailTemplate['message'] .= <<<HTML
			Server Information: {$serverInformation}
			<br>
			Maintenance File Path: {$options['maintenance-file']}
			<br>
			{$siteURL}
			HTML;
	}
}

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
	{
		if (mailEnabled() === true)
		{
			$mailInfo = MAIL_TEMPLATES['successful_deletion'];
			mail(implode(',', MAIL_RECIPIENTS), $mailInfo['subject'], $mailInfo['message'], MAIL_HEADERS);
		}
		exit('Deleted maintenance file.');
	}
	else
	{
		if (mailEnabled() === true)
		{
			$mailInfo = MAIL_TEMPLATES['failed_deletion'];
			mail(implode(',', MAIL_RECIPIENTS), $mailInfo['subject'], $mailInfo['message'], MAIL_HEADERS);
		}
		exit('FAILED TO DELETE MAINTENANCE FILE.');
	}
}

exit(0);

function mailEnabled(): bool
{
	return (MAIL_ENABLED === true && count(MAIL_RECIPIENTS) > 0);
}
