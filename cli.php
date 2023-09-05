<?php
require_once __DIR__ . '/vendor/autoload.php';

use CrusherRL\IcsLinksGenerator;

function storeAsFile(string $path, mixed $content): bool
{
	return file_put_contents($path, json_encode($content));
}

$start = '2023-08-15 15:00:00';
$end = '2023-08-15 16:30:00';
$summary = 'summary Hey!';
$location = 'location Hey!';
$description = 'description Hey!';
$allDay = 'false';

$generator = new IcsLinksGenerator($start, $end);
$specifics = ['yahoo', 'aol', 'outlook'];

// serialized
storeAsFile('examples/serialized/specific.json', $generator->generateSpecific($specifics));
storeAsFile('examples/serialized/all.json', $generator->generate());

// non-serialized
storeAsFile('examples/unserialized/specific.json', $generator->generateSpecific($specifics, false));
storeAsFile('examples/unserialized/all.json', $generator->generate(false));

