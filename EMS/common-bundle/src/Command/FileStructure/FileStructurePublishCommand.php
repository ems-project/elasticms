<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\File\FileStructure\FileStructureClientInterface;
use EMS\CommonBundle\Common\File\FileStructure\S3Client;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Common\Standard\Type;
use EMS\CommonBundle\Elasticsearch\Document\EMSSource;
use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileStructurePublishCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUBLISH;
    public const ARGUMENT_INDEX = 'index';
    public const ARGUMENT_IDENTIFIER = 'identifier';
    public const ARGUMENT_TARGET = 'target';
    public const OPTION_TERM_FIELD = 'term-field';
    public const OPTION_STRUCTURE_FIELD = 'structure-field';
    public const OPTION_S3_CREDENTIAL = 's3-credential';
    private string $index;
    private string $identifier;
    private string $termField;
    private string $structureField;
    private string $target;
    private ?string $s3Credential;

    public function __construct(private readonly ElasticaService $elasticaService, private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Publish a json encoded file structure into a S3 bucket');
        $this->addArgument(self::ARGUMENT_INDEX, InputArgument::REQUIRED, 'Elasticsearch index');
        $this->addArgument(self::ARGUMENT_IDENTIFIER, InputArgument::REQUIRED, 'Document identifier');
        $this->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target (folder or bucket)');
        $this
            ->addOption(self::OPTION_TERM_FIELD, null, InputOption::VALUE_OPTIONAL, 'Term field used to get the document', '_id')
            ->addOption(self::OPTION_STRUCTURE_FIELD, null, InputOption::VALUE_OPTIONAL, 'Document field path containing the JSON menu nested structure', '[structure]')
            ->addOption(self::OPTION_S3_CREDENTIAL, null, InputOption::VALUE_OPTIONAL, 'S3 credential in a JSON format')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->index = $this->getArgumentString(self::ARGUMENT_INDEX);
        $this->identifier = $this->getArgumentString(self::ARGUMENT_IDENTIFIER);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->termField = $this->getOptionString(self::OPTION_TERM_FIELD);
        $this->structureField = $this->getOptionString(self::OPTION_STRUCTURE_FIELD);
        $this->s3Credential = $this->getOptionStringNull(self::OPTION_S3_CREDENTIAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('File Structure - Publish');

        $query = $this->elasticaService->getTermsQuery($this->termField, [$this->identifier]);
        $search = $this->elasticaService->generateSearch([$this->index], $query);
        $result = $this->elasticaService->search($search);
        $result = $result->getResults();
        if (0 === \count($result)) {
            $this->io->warning(\sprintf('Document %s=%s not found in index %s', $this->termField, $this->identifier, $this->index));

            return self::EXECUTE_ERROR;
        }

        if (\count($result) > 1) {
            $this->io->warning(\sprintf('%d documents found for %s=%s in index %s', \count($result), $this->termField, $this->identifier, $this->index));

            return self::EXECUTE_ERROR;
        }
        $document = $result[0];
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $hash = Type::string($propertyAccessor->getValue($document->getData(), \sprintf('[%s]', EMSSource::FIELD_HASH)));
        $structureJson = Type::string($propertyAccessor->getValue($document->getData(), $this->structureField));
        $structure = JsonMenuNested::fromStructure($structureJson);
        $client = $this->getClient();
        $client->initSync($this->identifier, $hash);
        if ($client->isUpToDate()) {
            $this->io->writeln('Bucket is already up to date');

            return self::EXECUTE_SUCCESS;
        }

        $this->io->progressStart($structure->count());
        foreach ($structure->getIterator() as $item) {
            $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
            switch ($item->getType()) {
                case 'folder':
                    $client->createFolder($path, $item->getLabel());
                    break;
                default:
                    $stream = $this->storageManager->getStream(Type::string($propertyAccessor->getValue($item->getObject(), '[file][sha1]')));
                    $client->createFile($path, $stream, Type::string($propertyAccessor->getValue($item->getObject(), '[file][mimetype]')));
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $client->finalize();

        return self::EXECUTE_SUCCESS;
    }

    private function getClient(): FileStructureClientInterface
    {
        if (null === $this->s3Credential) {
            throw new \RuntimeException('Only S3 is currently implemented, --s3-credential must be defined');
        }
        $s3Client = new S3Client(Json::decode($this->s3Credential), $this->target);

        return $s3Client;
    }
}
