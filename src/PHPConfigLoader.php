<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);
$config = new PhpCsFixer\Config();
$rules = json_decode(file_get_contents('./php-cs-fixer.config.json'), true);

return $config->setRules($rules)->setFinder($finder);
