<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Document;

use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Search\Search;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends AbstractCommand
{
    private const string CONTENT_TYPE_ARGUMENT = 'content-type';
    private const string DATA_ARGUMENT = 'data';
    private const string QUERY_OPTION = 'query';
    private string $contentType;

    /** @var mixed[] */
    private array $query;

    /** @var mixed[] */
    private array $data;

    public function __construct(private readonly AdminHelper $adminHelper)
    {
        parent::__construct();
    }

    #[\Override]
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->adminHelper->setLogger(new ConsoleLogger($output));
        $this->contentType = $this->getArgumentString(self::CONTENT_TYPE_ARGUMENT);
        $this->data = Json::decode($this->getArgumentString(self::DATA_ARGUMENT));
        $this->query = Json::decode($this->getOptionString(self::QUERY_OPTION));
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument(self::CONTENT_TYPE_ARGUMENT, InputArgument::REQUIRED, 'Content-type\'s name');
        $this->addArgument(self::DATA_ARGUMENT, InputArgument::REQUIRED, 'Data to merge with in a JSON format');
        $this->addOption(self::QUERY_OPTION, null, InputOption::VALUE_OPTIONAL, 'Elasticsearch query to filter the documents in a JSON format', '{}');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coreApi = $this->adminHelper->getCoreApi();
        $searchApi = $coreApi->search();
        $this->io->title('Document - merge');

        if (!$coreApi->isAuthenticated()) {
            $this->io->error(\sprintf('Not authenticated for %s, run emsch:local:login', $coreApi->getBaseUrl()));

            return self::EXECUTE_ERROR;
        }
        $defaultAlias = $coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $contentTypeApi = $coreApi->data($this->contentType);
        $search = new Search([$defaultAlias], empty($this->query) ? null : $this->query);
        $search->setContentTypes([$this->contentType]);
        $search->setSources(['_id']);

        $this->io->progressStart($searchApi->count($search));
        foreach ($searchApi->scroll($search) as $hit) {
            $contentTypeApi->index($hit->getOuuid(), $this->data, true);
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        return self::EXECUTE_SUCCESS;
    }
}
