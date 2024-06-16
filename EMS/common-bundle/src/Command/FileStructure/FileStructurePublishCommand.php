<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
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

class FileStructurePublishCommand extends AbstractFileStructureCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUBLISH;
    public const ARGUMENT_INDEX = 'index';
    public const ARGUMENT_TARGET = 'target';
    public const OPTION_S3_CREDENTIAL = 's3-credential';
    private string $target;
    private ?string $s3Credential;
    private string $index;

    public function __construct(ElasticaService $elasticaService, private readonly StorageManager $storageManager)
    {
        parent::__construct($elasticaService);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Publish a json encoded file structure into a S3 bucket');
        $this->addArgument(self::ARGUMENT_INDEX, InputArgument::REQUIRED, 'Elasticsearch index');
        $this->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target (folder or bucket)');
        $this->addOption(self::OPTION_S3_CREDENTIAL, null, InputOption::VALUE_OPTIONAL, 'S3 credential in a JSON format');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->index = $this->getArgumentString(self::ARGUMENT_INDEX);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->s3Credential = $this->getOptionStringNull(self::OPTION_S3_CREDENTIAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('File Structure - Publish');
        $document = $this->getDocument($this->index);
        if (null === $document) {
            return self::EXECUTE_ERROR;
        }

        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $hash = Type::string($propertyAccessor->getValue($document->getData(), \sprintf('[%s]', EMSSource::FIELD_HASH)));
        $structureJson = Type::string($propertyAccessor->getValue($document->getData(), "[$this->structureField]"));
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
