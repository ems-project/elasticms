<?php

namespace EMS\CoreBundle\Command\Xliff;

use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CoreBundle\Commands;
use EMS\Helpers\File\TempFile;
use EMS\Xliff\Xliff\Extractor;
use EMS\Xliff\Xliff\Inserter;
use EMS\Xliff\Xliff\InsertionRevision;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergeCommand extends AbstractCommand
{
    protected static $defaultName = Commands::XLIFF_MERGE;
    private const ARGUMENT_SOURCE_FILE = 'SOURCE_FILE';
    private const ARGUMENT_FILE_WITH_TRANSLATION = 'FILE_WITH_TRANSLATIONS';
    private string $source;
    private string $translations;

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(self::ARGUMENT_SOURCE_FILE, InputArgument::REQUIRED, 'XLIFF File with fields to translated')
            ->addArgument(self::ARGUMENT_FILE_WITH_TRANSLATION, InputArgument::REQUIRED, 'File which may contains translation')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);

        $this->io->title('EMS Core - XLIFF - Merge');

        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE_FILE);
        $this->translations = $this->getArgumentString(self::ARGUMENT_FILE_WITH_TRANSLATION);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourceXliff = Inserter::fromFile($this->source);
        $translationsXliff = Inserter::fromFile($this->translations);
        $extracted = new Extractor('nl', 'de', $sourceXliff->getXliffVersion());
        $this->io->progressStart($sourceXliff->count());
        foreach ($sourceXliff->getDocuments() as $source) {
            $found = false;
            foreach ($translationsXliff->getDocuments() as $translation) {
                if ($translation->getOuuid() !== $source->getOuuid()) {
                    continue;
                }
                $found = true;
                if ($translation->getRevisionId() !== $source->getRevisionId()) {
                    $this->io->warning(\sprintf('Revision mismatched for document %s', $source->getOuuid()));
                }
                $this->retrieveNewTranslations($extracted, $source, $translation);
            }
            if (!$found) {
                $this->io->warning(\sprintf('Document %s not found', $source->getOuuid()));
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();

        $tempFile = TempFile::createNamed('xliff-merge');
        $extracted->saveXML($tempFile->path);
        $this->io->writeln(\sprintf('Extracted file: %s', $tempFile->path));

        return self::EXECUTE_SUCCESS;
    }

    private function retrieveNewTranslations(Extractor $extracted, InsertionRevision $source, InsertionRevision $translation): void
    {
        $source->mergeTranslations($extracted, $translation);
    }
}
