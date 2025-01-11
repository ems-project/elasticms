<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Storage\Service\StorageInterface;
use EMS\CommonBundle\Storage\StorageManager;
use EMS\CommonBundle\Twig\AssetRuntime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: Commands::CURL,
    description: 'Curl an internal resource.',
    hidden: false
)]
class CurlCommand extends AbstractCommand
{
    final public const string ARGUMENT_URL = 'url';
    final public const string ARGUMENT_FILENAME = 'filename';
    final public const string OPTION_METHOD = 'method';
    final public const string OPTION_BASE_URL = 'base-url';
    final public const string OPTION_SAVE = 'save';
    private ?SessionInterface $session = null;

    private string $url;
    private string $method;
    private string $filename;
    private ?string $baseUrl = null;
    private bool $save;

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher, private readonly ControllerResolverInterface $controllerResolver, private readonly RequestStack $requestStack, private readonly StorageManager $storageManager, private readonly AssetRuntime $assetRuntime)
    {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
        $this->addArgument(self::ARGUMENT_URL, InputArgument::REQUIRED, 'Absolute url to the resource');
        $this->addArgument(self::ARGUMENT_FILENAME, InputArgument::REQUIRED, 'Filename where to save the ouput');
        $this->addOption(self::OPTION_METHOD, null, InputOption::VALUE_OPTIONAL, 'HTTP method (GET, POST)', 'GET');
        $this->addOption(self::OPTION_BASE_URL, null, InputOption::VALUE_OPTIONAL, 'Base url, in order to generate a download link to the file');
        $this->addOption(self::OPTION_SAVE, null, InputOption::VALUE_NONE, 'Save the to the file storages');
    }

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        parent::initialize($input, $output);
        $this->url = $this->getArgumentString(self::ARGUMENT_URL);
        $this->filename = $this->getArgumentString(self::ARGUMENT_FILENAME);
        $this->method = $this->getOptionString(self::OPTION_METHOD);
        $this->baseUrl = $this->getOptionStringNull(self::OPTION_BASE_URL);
        $this->save = $this->getOptionBool(self::OPTION_SAVE);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->performRequest();
        $this->io->writeln(\sprintf('The file %s has been generated', $this->filename));

        if (!$this->save && null === $this->baseUrl) {
            return self::EXECUTE_SUCCESS;
        }

        $hash = $this->storageManager->saveFile($this->filename, StorageInterface::STORAGE_USAGE_ASSET);
        $this->io->writeln(\sprintf('The file has been saved with the id %s', $hash));

        if (null === $this->baseUrl) {
            return self::EXECUTE_SUCCESS;
        }

        $url = $this->getUrl($hash);
        $this->io->writeln(\sprintf('The file is available at %s', $url));

        return self::EXECUTE_SUCCESS;
    }

    protected function performRequest(): void
    {
        $kernel = new HttpKernel($this->eventDispatcher, $this->controllerResolver);
        $request = Request::create($this->url, $this->method);
        $request->setSession($this->getSession());
        $this->requestStack->push($request);
        $handle = \fopen($this->filename, 'w');
        if (false === $handle) {
            throw new \RuntimeException(\sprintf('Impossible to open the file %s', $this->filename));
        }
        \ob_start(function (string $buffer) use ($handle) {
            if (false === \fwrite($handle, $buffer)) {
                throw new \RuntimeException(\sprintf('Impossible to write to the file %s', $this->filename));
            }

            return '';
        });
        $response = $kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, false);
        $length = \ob_get_length();
        \ob_end_clean();

        $content = $response->getContent();
        if (0 === $length && false !== $content) {
            if (false === \fwrite($handle, $content)) {
                throw new \RuntimeException(\sprintf('Impossible to write to the file %s', $this->filename));
            }
        } elseif (0 === $length) {
            throw new \RuntimeException('Unexpected empty response');
        }
        \fclose($handle);
    }

    protected function getUrl(string $hash): string
    {
        $basename = \pathinfo($this->filename, PATHINFO_BASENAME);
        $symfonyFile = new File($this->filename, false);

        return $this->baseUrl.$this->assetRuntime->assetPath(
            [
                EmsFields::CONTENT_FILE_NAME_FIELD_ => $basename,
                EmsFields::CONTENT_FILE_HASH_FIELD_ => $hash,
                EmsFields::CONTENT_MIME_TYPE_FIELD_ => $symfonyFile->guessExtension() ?? 'application/bin',
            ],
            [],
            'ems_asset',
            EmsFields::CONTENT_FILE_HASH_FIELD,
            EmsFields::CONTENT_FILE_NAME_FIELD,
            EmsFields::CONTENT_MIME_TYPE_FIELD,
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    private function getSession(): SessionInterface
    {
        if (null !== $this->session) {
            return $this->session;
        }
        $this->session = $this->requestStack->getSession();

        return $this->session;
    }
}
