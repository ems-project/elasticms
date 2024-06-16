<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\PropertyAccess\PropertyAccessor;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\Helpers\File\File as FileHelper;
use EMS\Helpers\Standard\Json;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FileStructurePushCommand extends AbstractFileStructureCommand
{
    protected static $defaultName = Commands::FILE_STRUCTURE_PUSH;
    private const ARGUMENT_FOLDER = 'folder';
    private const OPTION_CONTENT_TYPE = 'content-type';
    private string $folderPath;
    private string $contentType;
    private CoreApiInterface $coreApi;

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
        $defaultAlias = $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType);
        $document = $this->getDocument($defaultAlias);
        if (null === $document) {
            return self::EXECUTE_ERROR;
        }
        $propertyAccessor = PropertyAccessor::createPropertyAccessor();
        $structureJson = Type::string($propertyAccessor->getValue($document->getData(), "[$this->structureField]"));
        $structure = JsonMenuNested::fromStructure($structureJson);
        $filesystem = new Filesystem();
        foreach ($structure->getIterator() as $item) {
            $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
            $relativePath = \implode('/', [$this->folderPath, $path]);
            if ($filesystem->exists($relativePath) && (\is_dir($relativePath) || 'folder' !== $item->getType())) {
                continue;
            }
            $parent = $$item->getParent();
            if (null === $parent) {
                throw new \RuntimeException(\sprintf('Parent not found for %s', $path));
            }
            $parent->removeChild($item);
        }
        $finder = new Finder();
        $finder->in($this->folderPath);
        foreach ($finder as $key => $file) {
            if (\is_file($key)) {
                $fileHelper = FileHelper::fromFilename($key);
                $mimeType ??= $fileHelper->mimeType;
                $filename ??= $fileHelper->name;
                $hash = $this->coreApi->file()->uploadFile($key, $mimeType, $filename);
                $type = 'file';
                $data = [
                    'file' => [
                        EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
                        EmsFields::CONTENT_FILE_SIZE_FIELD => \filesize($key),
                        EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
                        EmsFields::CONTENT_FILE_NAME_FIELD => $file->getFilename(),
                    ],
                ];
            } elseif (\is_dir($key)) {
                $type = 'folder';
                $data = [];
            } else {
                continue;
            }
            $data['label'] = $file->getFilename();
            $parent = null;
            $current = null;
            if ('' === $file->getRelativePath()) {
                $parent = $structure;
            }
            foreach ($structure->getIterator() as $item) {
                $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
                if ($path === $file->getRelativePathname()) {
                    $current = $item;
                }
                if ($path === $file->getRelativePath()) {
                    $parent = $item;
                }
            }
            if (null === $parent) {
                throw new \RuntimeException(\sprintf('Parent item not found for %s', $key));
            }
            if (null === $current) {
                $item = JsonMenuNested::create($type, $data);
                $parent->addChild($item);
            } else {
                $current->setObject($data);
            }
        }
        $this->coreApi->data($this->contentType)->index($document->getId(), [
            $this->structureField => Json::encode($structure->toArrayStructure()),
        ], true);

        return self::EXECUTE_SUCCESS;
    }
}
