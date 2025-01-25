<?php

if (!file_exists(__DIR__.'/elasticms-cli/src')) {
    exit(0);
}

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/build')
    ->in(__DIR__.'/elasticms-*/src')
    ->in(__DIR__.'/elasticms-*/tests')
    ->in(__DIR__.'/EMS/*/src')
    ->in(__DIR__.'/EMS/*/tests')
    ->exclude('/EMS/helpers/tmp')
    ->name(['release', 'translations'])
;

return new PhpCsFixer\Config()
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@PHP84Migration' => true,
        'declare_strict_types' => true,
        'phpdoc_separation' => ['skip_unlisted_annotations' => true],
        'native_function_invocation' => ['include' => ['@all']],
        'no_unused_imports' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
    ])
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__.'/.cache/.php-cs-fixer.cache')
    ->setFinder($finder)
;
