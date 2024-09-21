<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\FileStructure;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Standard\Hash;
use EMS\CommonBundle\Contracts\CoreApi\CoreApiInterface;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Json\JsonMenuNested;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\Helpers\File\File;
use EMS\Helpers\File\File as FileHelper;
use EMS\Helpers\Standard\Json;
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
    private Filesystem $filesystem;
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
        $this->filesystem = new Filesystem();
        $this->finder = (new Finder())->in($this->folderPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $document = $this->getDocument(
            index: $this->coreApi->meta()->getDefaultContentTypeEnvironmentAlias($this->contentType)
        );

        $structure = JsonMenuNested::fromStructure($document->getValue(\sprintf('[%s]', $this->structureField)));
        $this->syncStructureWithFilesystem($structure);

        $this->io->definitionList(
            ['total count' => $this->finder->count()],
            ['total folders' => $this->finder->directories()->count()],
            ['total files' => $this->finder->files()->count()],
        );

        $files = $this->getFiles();
        $uploadHashes  = $this->getNotUploadedHashes(...\array_keys($files));

        $uploadFiles = array_intersect_key($files, \array_flip($uploadHashes));

        $this->upload($uploadFiles);



        $test = 1;


//
//        $finder = new Finder();
//        $finder->in($this->folderPath);
//
//        $this->io->section(sprintf('Find files in "%s"', $this->folderPath));
//        $this->io->note(sprintf('found %s files and folders', $finder->count()));
//
//        $progressBar = $this->io->createProgressBar($finder->count());
//
//        //ask api for hash algo
//
//        foreach ($finder as $key => $file) {
//            if (\is_file($key)) {
//                $fileHelper = FileHelper::fromFilename($key);
//                $mimeType = $fileHelper->mimeType;
//                $filename = $fileHelper->name;
//                $hash = $this->coreApi->file()->uploadFile($key, $mimeType, $filename);

//                $type = 'file';
//                $data = [
//                    'file' => [
//                        EmsFields::CONTENT_FILE_HASH_FIELD => $hash,
//                        EmsFields::CONTENT_FILE_SIZE_FIELD => \filesize($key),
//                        EmsFields::CONTENT_MIME_TYPE_FIELD => $mimeType,
//                        EmsFields::CONTENT_FILE_NAME_FIELD => $file->getFilename(),
//                    ],
//                ];
//            } elseif (\is_dir($key)) {
//                $type = 'folder';
//                $data = [];
//            } else {
//               // continue;
//            }
//
//            $progressBar->advance();

//            $data['label'] = $file->getFilename();
//            $parent = null;
//            $current = null;
//            if ('' === $file->getRelativePath()) {
//                $parent = $structure;
//            }
//            foreach ($structure->getIterator() as $item) {
//                $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
//                if ($path === $file->getRelativePathname()) {
//                    $current = $item;
//                }
//                if ($path === $file->getRelativePath()) {
//                    $parent = $item;
//                }
//            }
//            if (null === $parent) {
//                throw new \RuntimeException(\sprintf('Parent item not found for %s', $key));
//            }
//            if (null === $current) {
//                $item = JsonMenuNested::create($type, $data);
//                $parent->addChild($item);
//            } else {
//                $current->setObject($data);
//            }
//        }

//        $progressBar->finish();

//        $this->coreApi->data($this->contentType)->index($document->getId(), [
//            $this->structureField => Json::encode($structure->toArrayStructure()),
//        ], true);

        return self::EXECUTE_SUCCESS;
    }

    /**
     * @param File[] $files
     */
    private function upload(array $files): void
    {
        $this->io->section('Upload files');
        $progressBar = $this->io->createProgressBar(\count($files));

        foreach ($files as $file) {
            $this->coreApi->file()->uploadStream(
                stream: $file->getStream(),
                filename: $file->name,
                mimeType: $file->mimeType,
                head: false
            );

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();
    }

    /**
     * @return array<string, File>
     */
    private function getFiles(): array
    {
        $this->io->section('Get files (calculating hashes)');

        $files = [];
        $hashAlgo = $this->coreApi->file()->getHashAlgo();

        $progressBar = $this->io->createProgressBar($this->finder->files()->count());

        foreach ($this->finder->files() as $fileInfo) {
            $file = new File($fileInfo);
            $hash = $hashAlgo($file->getContents());
            $files[$hash] = $file;

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine();

        $this->io->note(sprintf('Found %d unique files', \count($files)));
        $this->io->newLine();

        return $files;
    }

    /**
     * @return string[]
     */
    private function getNotUploadedHashes(string ...$fileHashes): array
    {
        $this->io->section('Get not uploaded hashes');

        $chunks = (int) ceil(count($fileHashes) / 1000);
        $progressBar = $this->io->createProgressBar($chunks);

        $result = [];
        foreach ($this->coreApi->file()->heads(...$fileHashes) as $missing) {
            $result = [...$result, ...$missing];
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->io->newLine();

        $this->io->note(sprintf('Found %d not uploaded hashes', \count($result)));

        return $result;
    }

    private function syncStructureWithFilesystem(JsonMenuNested $structure): void
    {
        foreach ($structure->getIterator() as $item) {
            $path = \implode('/', $item->getPath(fn (JsonMenuNested $item) => $item->getLabel()));
            $relativePath = \implode('/', [$this->folderPath, $path]);

            if ($this->filesystem->exists($relativePath) && (\is_dir($relativePath) || 'folder' !== $item->getType())) {
                continue;
            }

            $item->getParent()?->removeChild($item);
        }
    }
}
