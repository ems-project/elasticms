<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Index;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Cluster\SimpleIndexClient;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Standard\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::INDEX_SYNCHRONIZE,
    description: 'Copy, or keep in sync, an ElasticMS indexes into another one',
    hidden: false
)]
class SynchronizeCommand extends AbstractCommand
{
    private const string ARGUMENT_SOURCE = 'source';
    private const string ARGUMENT_TARGET = 'target';
    private const string OPTION_BULK_SIZE = 'bulk-size';
    public const string OPTION_SOURCE_HEADERS = 'source-headers';
    public const string OPTION_TARGET_HEADERS = 'target-headers';
    private string $source;
    private string $target;
    private int $bulkSize;
    /**
     * @var string[]
     */
    private array $sourceHeaders;
    /**
     * @var string[]
     */
    private array $targetHeaders;

    public function __construct(public readonly LoggerInterface $logger)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_SOURCE,
                InputArgument::REQUIRED,
                'The source to copy'
            )
            ->addArgument(
                self::ARGUMENT_TARGET,
                InputArgument::REQUIRED,
                'The target to copy'
            )
            ->addOption(
                self::OPTION_BULK_SIZE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Number of bulk size to copy',
                100
            )
            ->addOption(
                self::OPTION_SOURCE_HEADERS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Extra headers of the copy client (JSON encoded)',
                '[]'
            )
            ->addOption(
                self::OPTION_TARGET_HEADERS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Extra headers of the target client (JSON encoded)',
                '[]'
            );
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->bulkSize = $this->getOptionInt(self::OPTION_BULK_SIZE);
        $this->targetHeaders = Json::decode($this->getOptionString(self::OPTION_TARGET_HEADERS));
        $this->sourceHeaders = Json::decode($this->getOptionString(self::OPTION_SOURCE_HEADERS));
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Synchronizing %s to %s', $this->source, $this->target));
        $sourceClient = SimpleIndexClient::create($this->source, $this->targetHeaders);
        if (!$sourceClient->isDefined()) {
            throw new \RuntimeException('Source index not found');
        }
        $this->io->info(\sprintf('Source index %s', $sourceClient->getIndex()));

        $targetClient = SimpleIndexClient::create($this->target, $this->sourceHeaders);

        return self::EXECUTE_SUCCESS;
    }
}
