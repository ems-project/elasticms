<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Website;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::WEBSITE_REWRITE_URLS,
    description: 'Rewrite URLs in a static website and export the result to a target directory.',
    hidden: false
)]
class RewriteUrlsCommand extends AbstractCommand
{
    public const ARGUMENT_SOURCE = 'source';
    public const ARGUMENT_TARGET = 'target';
    public const OPTION_BASE_URLS = 'base-urls';
    private string $source;
    private string $target;
    /** @var string[] */
    private array $baseUrls;

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_SOURCE, InputArgument::REQUIRED, 'Source directory of the static website')
            ->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target directory where the rewritten website is exported')
            ->addOption(self::OPTION_BASE_URLS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Base URLs to rewrite (can be used multiple times)')
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->baseUrls = $this->getOptionStringArray(self::OPTION_BASE_URLS, false);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - Website - Rewrite URLs');

        return self::EXECUTE_SUCCESS;
    }
}
