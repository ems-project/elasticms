<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Common\File\FileStructure\FileStructureClientInterface;
use EMS\CommonBundle\Common\File\FileStructure\S3Client;
use EMS\CommonBundle\Exception\FileStructureNotSyncException;
use EMS\CommonBundle\Storage\Archive;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FileStructurePublishCommand extends AbstractCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUBLISH;
    public const ARGUMENT_ARCHIVE_HASH = 'hash';
    public const ARGUMENT_TARGET = 'target';
    public const OPTION_S3_CREDENTIAL = 's3-credential';
    public const OPTION_FORCE = 'force';
    private string $target;
    private ?string $s3Credential;
    private string $archiveHash;
    private bool $force;

    public function __construct(private readonly StorageManager $storageManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Publish the file structure of an ElasticMS archive into a S3 bucket');
        $this->addArgument(self::ARGUMENT_ARCHIVE_HASH, InputArgument::REQUIRED, 'Elasticsearch index');
        $this->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target (S3 bucket)');
        $this->addOption(self::OPTION_S3_CREDENTIAL, null, InputOption::VALUE_OPTIONAL, 'S3 credential in a JSON format');
        $this->addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'The target is synchronize even if the target looks already synchronized or if the target looks out of sync');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->archiveHash = $this->getArgumentString(self::ARGUMENT_ARCHIVE_HASH);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $this->s3Credential = $this->getOptionStringNull(self::OPTION_S3_CREDENTIAL);
        $this->force = $this->getOptionBool(self::OPTION_FORCE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('File Structure - Publish');

        $algo = $this->storageManager->getHashAlgo();
        $archive = Archive::fromStructure($this->storageManager->getContents($this->archiveHash), $algo);

        $client = $this->getClient();
        $client->initSync($this->archiveHash);
        try {
            if (!$this->force && $client->isUpToDate()) {
                $this->io->writeln('Bucket is already up to date');

                return self::EXECUTE_SUCCESS;
            }
        } catch (FileStructureNotSyncException $e) {
            $this->io->error(\sprintf('The file structure might contain manual changes, use the --force option: %s', $e->getMessage()));

            return self::EXECUTE_SUCCESS;
        }

        $this->io->progressStart($archive->getCount());
        foreach ($archive->iterator() as $item) {
            $stream = $this->storageManager->getStream($item->hash);
            $client->createFile($item->filename, $stream, $item->type);
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
