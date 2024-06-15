#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use App\Admin\Kernel;
use EMS\CommonBundle\Common\Converter;
use Symfony\Bridge\Twig\Translation\TwigExtractor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use Symfony\Component\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;

const IGRNORE_PATHS = [
    'Command',
    'DependencyInjection',
    'Entity',
    'Repository',
    '/Resources\/(public|config|DoctrineMigrations)/',
];

$command = function (InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $io->title('Build: extract translations');

    $locale = $input->getArgument('locale');
    $stopWatch = new Stopwatch();
    $stopWatch->start('build');

    (new Dotenv())->load(__DIR__.'/../elasticms-admin/.env');
    $adminKernel = new Kernel('dev', true);
    $adminKernel->boot();
    $bundleDir = $adminKernel->getBundle($input->getArgument('bundle'))->getPath();
    /** @var Environment $twig */
    $twig = $adminKernel->getContainer()->get('twig');
    $io->section($bundleDir);

    $extractedCatalogue = new MessageCatalogue($locale);
    $finder = (new Finder())
        ->ignoreUnreadableDirs()
        ->ignoreVCSIgnored(true)
        ->notPath(IGRNORE_PATHS)
        ->in($bundleDir);
    $extractors = [
       ['Parsing php files', '*.php', new PhpExtractor()],
       ['Parsing twig files', '*.twig', new TwigExtractor($twig)],
    ];

    foreach ($extractors as list($title, $name, $extractor)) {
        /* @var ExtractorInterface $extractor */
        $io->writeln($title);
        $files = (clone $finder)->name($name);
        $progressBar = $io->createProgressBar($files->count());

        foreach ($files as $file) {
            $extractor->extract($file->getRealPath(), $extractedCatalogue);
            $progressBar->advance();
        }

        $progressBar->finish();
        $io->newLine();
    }

    $buildStopWatch = $stopWatch->stop('build');

    $io->newLine();
    $io->listing([
        \sprintf('Duration: %d s', $buildStopWatch->getDuration() / 1000),
        \sprintf('Memory: %s', Converter::formatBytes($buildStopWatch->getMemory())),
    ]);

    return Command::SUCCESS;
};

(new SingleCommandApplication())
    ->setName('Build: extract translations')
    ->addArgument('locale', InputArgument::REQUIRED, 'locale')
    ->addArgument('bundle', InputArgument::REQUIRED, 'bundle')
    ->setCode($command)
    ->run();
