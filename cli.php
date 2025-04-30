<?php
require_once __DIR__ . '/vendor/autoload.php';

use CrusherRL\IcsLinksGenerator;

function storeAsFile(string $path, mixed $content): bool
{
	return file_put_contents($path, json_encode($content));
}

$url = 'https://example.com/create-event?summary=Meeting&description=Discuss+project&location=Office&start=20250430T100000Z&end=20250430T110000Z&all_day=true';

$start = '2023-08-15 15:00:00';
$end = '2023-08-15 16:30:00';
$summary = 'Welcome on Board!';
$location = 'any location';
$description = 'Let\'s talk about this stuff';
$allDay = 'false';

$specifics = [IcsLinksGenerator::YAHOO, IcsLinksGenerator::AOL, IcsLinksGenerator::OUTLOOK_MOBILE];

$generator = new IcsLinksGenerator($start, $end);
$generator2 = IcsLinksGenerator::fromUrl($url);
$generator3 = IcsLinksGenerator::make([
	'DTSTART'     => $start,
	'DTEND'       => $end,
	'SUMMARY'     => $summary,
	'LOCATION'    => $location,
	'DESCRIPTION' => $description,
	'ALLDAY'      => $allDay
]);

$generator->setLabels([IcsLinksGenerator::OUTLOOK_MOBILE => 'Mobile Outlook']);

// serialized
storeAsFile('examples/serialized/specific.json', $generator->generateSpecific($specifics));
storeAsFile('examples/serialized/all.json',  $generator->generate());

// from URL
storeAsFile('examples/url/specific.json', $generator2->generateSpecific($specifics, false));
storeAsFile('examples/url/all.json', $generator2->generate());

// non-serialized
storeAsFile('examples/unserialized/specific.json', $generator3->generateSpecific($specifics, false));
storeAsFile('examples/unserialized/all.json', $generator3->generate(false));

