<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command\Website;

use Dom\HTMLDocument;
use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\Standard\Json;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(
    name: Commands::WEBSITE_REWRITE_URLS,
    description: 'Rewrite URLs in a static website and export the result to a target directory.',
    hidden: false
)]
class RewriteUrlsCommand extends AbstractCommand
{
    public const string ARGUMENT_SOURCE = 'source';
    public const string ARGUMENT_TARGET = 'target';
    public const string OPTION_BASE_URLS = 'base-urls';
    private const array URL_ATTRIBUTES = ['href', 'src', 'poster', 'action', 'srcset', 'imagesrcset'];
    private string $source;
    private string $target;
    /** @var string[] without trailing slash (so '/' becomes ''), longest first */
    private array $baseUrls;
    private Filesystem $filesystem;

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARGUMENT_SOURCE, InputArgument::REQUIRED, 'Source directory of the static website')
            ->addArgument(self::ARGUMENT_TARGET, InputArgument::REQUIRED, 'Target directory where the rewritten website is exported')
            ->addOption(self::OPTION_BASE_URLS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Base URLs to rewrite (can be used multiple times)', ['/'])
        ;
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->source = $this->getArgumentString(self::ARGUMENT_SOURCE);
        $this->target = $this->getArgumentString(self::ARGUMENT_TARGET);
        $baseUrls = \array_map(fn (string $baseUrl) => \rtrim($baseUrl, '/'), $this->getOptionStringArray(self::OPTION_BASE_URLS, false));
        \usort($baseUrls, fn (string $a, string $b) => \strlen($b) <=> \strlen($a));
        $this->baseUrls = \array_values(\array_unique($baseUrls));
        $this->filesystem = new Filesystem();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('EMS - Website - Rewrite URLs');

        if (!\is_dir($this->source)) {
            $this->io->error(\sprintf('The source "%s" is not a directory.', $this->source));

            return self::EXECUTE_ERROR;
        }

        if (!$this->prepareTarget()) {
            return self::EXECUTE_ERROR;
        }

        $finder = new Finder();
        $finder->files()->in($this->source)->ignoreDotFiles(false)->ignoreVCS(false);

        $this->io->progressStart($finder->count());
        foreach ($finder as $file) {
            switch (\strtolower($file->getExtension())) {
                case 'html':
                case 'htm':
                    $this->rewriteHtml($file);
                    break;
                case 'css':
                    $this->rewriteCss($file);
                    break;
                case 'js':
                case 'mjs':
                case 'cjs':
                    $this->rewriteJs($file);
                    break;
                case 'json':
                case 'webmanifest':
                    $this->rewriteJson($file);
                    break;
                default:
                    $this->filesystem->copy(
                        $file->getPathname(),
                        Path::join($this->target, $file->getRelativePathname()),
                        true
                    );
            }
            $this->io->progressAdvance();
        }
        $this->io->progressFinish();
        $this->io->newLine(2);

        return self::EXECUTE_SUCCESS;
    }

    private function prepareTarget(): bool
    {
        if (!$this->filesystem->exists($this->target)) {
            if (!$this->io->confirm(\sprintf('The target directory "%s" does not exist, do you want to create it?', $this->target), false)) {
                $this->io->warning('Target directory not created, aborting.');

                return false;
            }
            $this->filesystem->mkdir($this->target);

            return true;
        }

        if (!\is_dir($this->target)) {
            $this->io->error(\sprintf('The target "%s" is not a directory.', $this->target));

            return false;
        }

        $finder = new Finder();
        $finder->in($this->target)->ignoreDotFiles(false)->ignoreVCS(false)->depth('== 0');
        if (!$finder->hasResults()) {
            return true;
        }

        if (!$this->io->confirm(\sprintf('The target directory "%s" is not empty, do you want to delete its content?', $this->target), false)) {
            $this->io->warning('Target directory not emptied, aborting.');

            return false;
        }
        $this->filesystem->remove($finder);

        return true;
    }

    private function rewriteHtml(SplFileInfo $file): void
    {
        $document = HTMLDocument::createFromString($file->getContents(), \LIBXML_NOERROR);
        $currentDir = Path::join((string) \realpath($this->source), \dirname($file->getRelativePathname()));

        foreach ($document->querySelectorAll('*') as $element) {
            $urls = [];
            foreach ($element->attributes as $attribute) {
                if (\in_array($attribute->name, self::URL_ATTRIBUTES, true) || \str_starts_with($attribute->name, 'data-')) {
                    $urls[$attribute->name] = $attribute->value;
                }
            }
            foreach ($urls as $name => $value) {
                $relative = \str_ends_with($name, 'srcset')
                    ? $this->rewriteSrcset($value, $currentDir)
                    : $this->toRelativeUrl($value, $currentDir);
                if (null !== $relative && $relative !== $value) {
                    $element->setAttribute($name, $relative);
                }
            }
        }

        $targetFile = Path::join($this->target, $file->getRelativePathname());
        $this->filesystem->dumpFile($targetFile, $document->saveHtml());
    }

    private function rewriteSrcset(string $srcset, string $currentDir): string
    {
        return \preg_replace_callback(
            '/(\s*)(?:([^\s,]\S*,+)(?=\s|$)|([^\s,]\S*)(\s[^,]*)?(?:,|$))/',
            function (array $m) use ($currentDir) {
                if ('' !== ($m[2] ?? '')) {
                    $url = \rtrim($m[2], ',');
                    $rest = \substr($m[2], \strlen($url));
                } else {
                    $url = $m[3] ?? '';
                    $rest = ($m[4] ?? '').(\str_ends_with($m[0], ',') ? ',' : '');
                }

                return $m[1].($this->toRelativeUrl($url, $currentDir) ?? $url).$rest;
            },
            $srcset
        ) ?? $srcset;
    }

    private function rewriteCss(SplFileInfo $file): void
    {
        $currentDir = Path::join((string) \realpath($this->source), \dirname($file->getRelativePathname()));
        $replace = fn (array $m) => $m[1].($this->toRelativeUrl($m[3], $currentDir) ?? $m[3]).$m[4];

        $css = $file->getContents();
        $css = \preg_replace_callback('/(url\(\s*(["\']?))([^"\')\s]+)(\2\s*\))/i', $replace, $css) ?? $css;
        $css = \preg_replace_callback('/(@import\s+(["\']))([^"\']+)(\2)/i', $replace, $css) ?? $css;

        $this->filesystem->dumpFile(Path::join($this->target, $file->getRelativePathname()), $css);
    }

    private function rewriteJs(SplFileInfo $file): void
    {
        $currentDir = Path::join((string) \realpath($this->source), \dirname($file->getRelativePathname()));

        $js = \preg_replace_callback(
            '/((?:\bfrom|\bimport)\s*\(?\s*(["\']))([^"\'\n]+)(\2)/',
            function (array $m) use ($currentDir) {
                $relative = $this->toRelativeUrl($m[3], $currentDir);
                if (null !== $relative && !\str_starts_with($relative, '.')) {
                    $relative = './'.$relative;
                }

                return $m[1].($relative ?? $m[3]).$m[4];
            },
            $file->getContents()
        ) ?? $file->getContents();

        $this->filesystem->dumpFile(Path::join($this->target, $file->getRelativePathname()), $js);
    }

    private function rewriteJson(SplFileInfo $file): void
    {
        $currentDir = Path::join((string) \realpath($this->source), \dirname($file->getRelativePathname()));

        $json = \preg_replace_callback(
            '/"((?:[^"\\\\]|\\\\.)*+)"(\s*:)?/s',
            function (array $m) use ($currentDir) {
                if (isset($m[2])) {
                    return $m[0];
                }
                try {
                    $value = Json::mixedDecode('"'.$m[1].'"');
                } catch (\RuntimeException) {
                    return $m[0];
                }
                $relative = \is_string($value) ? $this->toRelativeUrl($value, $currentDir) : null;

                return null === $relative ? $m[0] : Json::encode($relative, true);
            },
            $file->getContents()
        ) ?? $file->getContents();

        $this->filesystem->dumpFile(Path::join($this->target, $file->getRelativePathname()), $json);
    }

    private function toRelativeUrl(string $url, string $currentDir): ?string
    {
        $rest = null;
        foreach ($this->baseUrls as $baseUrl) {
            if (!\str_starts_with($url, $baseUrl)) {
                continue;
            }
            $candidate = \substr($url, \strlen($baseUrl));
            $valid = '' === $baseUrl
                ? \str_starts_with($candidate, '/') && !\str_starts_with($candidate, '//')
                : '' === $candidate || 1 === \preg_match('/^[\/?#]/', $candidate);
            if ($valid) {
                $rest = $candidate;
                break;
            }
        }
        if (null === $rest) {
            return null;
        }

        $suffix = '';
        if (1 === \preg_match('/^([^?#]*)([?#].*)$/s', $rest, $matches)) {
            [, $rest, $suffix] = $matches;
        }
        $path = \rawurldecode($rest);
        if ('' === $path) {
            $path = '/';
        }
        if (\in_array('..', \explode('/', $path), true)) {
            return null;
        }

        $sourceRoot = (string) \realpath($this->source);
        $absolute = Path::join($sourceRoot, $path);
        $exists = \is_file($absolute)
            || (\is_dir($absolute) && (\is_file(Path::join($absolute, 'index.html')) || \is_file(Path::join($absolute, 'index.htm'))));
        if (!$exists) {
            return null;
        }

        $relative = Path::makeRelative($absolute, $currentDir);
        $segments = \array_map(fn (string $segment) => \str_replace('%2C', ',', \rawurlencode($segment)), \array_filter(\explode('/', $relative), fn (string $s) => '' !== $s));
        $relative = \implode('/', $segments);
        if (\str_ends_with($path, '/') || \is_dir($absolute)) {
            $relative = '' === $relative ? './' : $relative.'/';
        } elseif ('' === $relative) {
            $relative = \rawurlencode(\basename($absolute));
        }

        return $relative.$suffix;
    }
}
