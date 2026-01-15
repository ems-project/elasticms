<?php

declare(strict_types=1);

$finder = TwigCsFixer\File\Finder::create()
    ->in(__DIR__.'/../EMS/admin-ui-bundle/templates/bootstrap5');

return new TwigCsFixer\Config\Config()
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/../.cache/.twig-cs-fixer.cache');
