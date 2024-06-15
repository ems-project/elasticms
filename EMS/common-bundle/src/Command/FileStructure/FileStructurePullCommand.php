<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Common\Standard\Type;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Service\ElasticaService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileStructurePullCommand extends AbstractFileStructureCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PULL;
    private const ARGUMENT_FOLDER = 'folder';
    private const OPTION_CONTENT_TYPE = 'content-type';
    private string $folderPath;
    private string $contentType;
    private CoreApiInterface $coreApi;
    private Finder $finder;

    public function __construct(ElasticaService $elasticaService, private readonly AdminHelper $adminHelper)
    {
        parent::__construct($elasticaService);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->setDescription('Push a JSON encoded file structure into a folder (and overwrite it)');
        $this->addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'Target folder');
        $this
            ->addOption(self::OPTION_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Content type name', 'directory')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->folderPath = $this->getArgumentString(self::ARGUMENT_FOLDER);
        $this->contentType = $this->getOptionString(self::OPTION_CONTENT_TYPE);
        $this->coreApi = $this->adminHelper->getCoreApi();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->collectExistingFiles();
        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $document = $this->getDocument($defaultAlias);
        if (null === $document) {
            return self::EXECUTE_ERROR;
        }
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $structureJson = Type::string($propertyAccessor->getValue($document->getData(), "[$this->structureField]"));
        $structure = JsonMenuNested::fromStructure($structureJson);
        $this->io->progressStart($structure->count());
        $paths = [];
        foreach ($structure->getIterator() as $item) {
            $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
            $relativePath = \implode('/', [$this->folderPath, $path]);
            $paths[$path] = $path;
            switch ($item->getType()) {
                case 'folder':
                    if (\is_file($relativePath) && !\is_dir($relativePath)) {
                        \unlink($relativePath);
                    }
                    if (!\is_dir($relativePath)) {
                        \mkdir($relativePath);
                    }
                    break;
                default:
                    $fileHash = Type::string($propertyAccessor->getValue($item->getObject(), '[file][sha1]'));
                    if (\is_file($relativePath) && $fileHash === \sha1_file($relativePath)) {
                        break;
                    }
                    $file = $this->coreApi->file()->downloadFile($fileHash);
                    \rename($file, $relativePath);
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $filesystem = new Filesystem();
        $toBeDelete = \array_filter(\array_map(fn ($file) => $file->getRelativePathname(), \iterator_to_array($this->finder)), fn (string $path) => !isset($paths[$path]));
        $filesystem->remove(\array_keys($toBeDelete));

        return self::EXECUTE_SUCCESS;
    }

    private function collectExistingFiles(): void
    {
        $this->finder = new Finder();
        $this->finder->in($this->folderPath);
    }
}
