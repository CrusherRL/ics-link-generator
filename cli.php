<?php
require_once __DIR__ . '/vendor/autoload.php';

use CrusherRL\IcsLinksGenerator;

$start = '2023-08-15 15:00:00';
$end = '2023-08-15 16:30:00';
$summary = 'summaryHey!';
$location = 'locationHey!';
$description = 'descriptionHey!';
$allDay = 'true';

$generator = new IcsLinksGenerator($start, $end, $summary, $location, $description, $allDay);

$data = $generator->getAll();

$generator->storeAsFile('tmp.json', $data);