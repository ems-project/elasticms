<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\ClientHelperBundle\Helper\Environment\EnvironmentHelper;
use EMS\ClientHelperBundle\Helper\Local\LocalHelper;
use EMS\ClientHelperBundle\Helper\Routing\RoutingFile;
use EMS\ClientHelperBundle\Helper\Translation\TranslationFiles;
use Symfony\Bridge\Twig\Translation\TwigExtractor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment as TwigEnvironment;

final class NormalizeCommand extends AbstractLocalCommand
{
    public function __construct(
        EnvironmentHelper $environmentHelper,
        LocalHelper $localHelper,
        private readonly TwigEnvironment $twig,
    ) {
        parent::__construct($environmentHelper, $localHelper);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - normalize');

        if (!$this->environment->isLocalPulled()) {
            $this->io->error('No local environment found, run emsch:local:pull first');

            return self::EXECUTE_ERROR;
        }

        try {
            $keys = $this->extractTranslationKeys();
            $translationFiles = $this->normalizeTranslations($keys);
            $routesNormalized = RoutingFile::normalize($this->localEnvironment->getDirectory());

            $this->io->success(\sprintf(
                'Normalized %d translation file(s), %d translation key(s) and %s routes.yaml',
                $translationFiles,
                \count($keys),
                $routesNormalized ? 'the' : 'no',
            ));

            return self::EXECUTE_SUCCESS;
        } catch (\Throwable $throwable) {
            $this->io->error($throwable->getMessage());

            return self::EXECUTE_ERROR;
        }
    }

    /**
     * @return array<string, true>
     */
    private function extractTranslationKeys(): array
    {
        $files = [];
        $finder = Finder::create()
            ->files()
            ->in($this->localEnvironment->getDirectory())
            ->name('*.twig');

        foreach ($finder as $file) {
            $files[] = $file->getPathname();
        }

        $catalogue = new MessageCatalogue('en');
        new TwigExtractor($this->twig)->extract($files, $catalogue);

        $keys = [];
        foreach ($catalogue->all() as $messages) {
            foreach ($messages as $key => $message) {
                $keys[(string) $key] = true;
            }
        }
        \ksort($keys, \SORT_STRING);

        return $keys;
    }

    /**
     * @param array<string, true> $keys
     */
    private function normalizeTranslations(array $keys): int
    {
        $translationFiles = new TranslationFiles($this->localEnvironment->getDirectory());

        return $translationFiles->normalize($keys);
    }
}
