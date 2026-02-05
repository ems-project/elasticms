<?php

declare(strict_types=1);

namespace App\CLI\Command\MediaLibrary;

use App\CLI\Client\MediaLibrary\MediaLibrarySyncOptions;
use App\CLI\Commands;
use EMS\CommonBundle\Common\Admin\AdminHelper;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Contracts\ExpressionServiceInterface;
use EMS\CommonBundle\Contracts\File\FileReaderInterface;
use RectorPrefix202601\Symfony\Component\Console\Input\InputArgument;
use RectorPrefix202601\Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: Commands::UPDATE_FILE_LINKS,
    description: 'Convert ems file links into ems object link', // TODO Verify script description 
    hidden: false
)]
final class UpdateFileLinks extends AbstractCommand
{
    private const string ARGUMENT_FOLDER = 'folder';
    private const string OPTION_FOLDER_FIELD = 'folder-field';
    private const string OPTION_CONTENT_TYPE = 'content-type';
    private const string OPTION_FORCE = 'force';
    
    private MediaLibrarySyncOptions $options;

    #[\Override]
    public function __construct(
        private readonly AdminHelper $adminHelper,
        private readonly FileReaderInterface $fileReader,
        private readonly ExpressionServiceInterface $expressionService
    ){
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this 
            -> addArgument(self::ARGUMENT_FOLDER, InputArgument::REQUIRED, 'folder')
            -> addOption(self::OPTION_FOLDER_FIELD, null, InputOption::VALUE_OPTIONAL, 'Media Library folder field (default: media_folder)', 'media_folder')
            -> addOption(self::OPTION_CONTENT_TYPE, null, InputOption::VALUE_OPTIONAL, 'Media Library content type (default: media_file)', 'media_file')
            -> addOption(self::OPTION_FORCE, null, InputOption::VALUE_NONE, 'Dry run if undefined') //TODO Find better description
        ;
    }
    
    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        
        $this->options = new MediaLibrarySyncOptions(
            //TODO If I have to put all args in this method, do I have to declare them before (in the object attributes) or can I simply put a bunch of nulls and false?
            folder: $this->getArgumentString(self::ARGUMENT_FOLDER),
            contentType: $this->getOptionString(self::OPTION_CONTENT_TYPE),
            folderField: $this->getOptionString(self::OPTION_FOLDER_FIELD),
            pathField: null,
            fileField: null,
            metaDataFile: null,
            locateRowExpression: null,
            targetFolder: null,
            dryRun: false,
            onlyMissingFile: false,
            onlyMetadataFile: false,
            hashFolder: false,
            hashMetaDataFile: false,
            forceExtract: false,
            maxContentSize: false,
            maxFileSizeExtract: false,
        );
        
    }
    
    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title(\sprintf('Convert ems file links into ems object link in %s', 'content type name')); //TODO change ctname
        
        return self::EXECUTE_SUCCESS;
    }
}
