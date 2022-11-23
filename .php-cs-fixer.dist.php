<?php

if (!file_exists(__DIR__.'/elasticms-cli/src')) {
    exit(0);
}

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/elasticms-cli/src')
    ->in(__DIR__.'/elasticms-cli/tests')
    ->in(__DIR__.'/EMS/client-helper-bundle/src')
    ->in(__DIR__.'/EMS/client-helper-bundle/tests')
    ->in(__DIR__.'/EMS/common-bundle/src')
    ->in(__DIR__.'/EMS/common-bundle/tests')
    ->in(__DIR__.'/EMS/form-bundle/src')
    ->in(__DIR__.'/EMS/form-bundle/tests')
    ->in(__DIR__.'/EMS/helpers/src')
    ->in(__DIR__.'/EMS/helpers/tests')
    ->exclude('/EMS/helpers/tmp')
;

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@Symfony' => true,
        //'declare_strict_types' => true,
        //'final_class' => true,
        'native_function_invocation' => ['include' => ['@all']],
        'no_unused_imports' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
