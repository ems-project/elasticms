<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Command;

use EMS\CommonBundle\Commands;
use EMS\CommonBundle\Common\Command\AbstractCommand;
use EMS\Helpers\File\File;
use EMS\Helpers\Standard\Json;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;
use Twig\TemplateWrapper;

use function Symfony\Component\String\u;

class BatchCommand extends AbstractCommand
{
    protected static $defaultName = Commands::BATCH;
    private const ARGUMENT_TEMPLATE = 'template';
    private const OPTION_CONTEXT = 'context';

    public function __construct(private readonly Environment $twig)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();
        $this
            ->setDescription('Run commands defined in twig')
            ->addArgument(self::ARGUMENT_TEMPLATE, InputArgument::REQUIRED, 'template name, path or twig code')
            ->addOption(self::OPTION_CONTEXT, null, InputOption::VALUE_REQUIRED, 'context passed to twig')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('EMS - Batch');

        /** @var Application $application */
        $application = $this->getApplication();
        $application->setAutoExit(false);

        try {
            $context = $this->getOptionString(self::OPTION_CONTEXT, '{}');
            if (!Json::isJson($context)) {
                throw new \RuntimeException('Invalid context passed');
            }

            $templateName = $this->getArgumentString(self::ARGUMENT_TEMPLATE);
            $template = $this->getTemplate($templateName);

            $renderContext = Json::decode($context);
            $render = $template->hasBlock('execute') ?
                $template->renderBlock('execute', $renderContext) : $template->render($renderContext);

            if (!Json::isJson($render)) {
                throw new \RuntimeException(\sprintf('Template not returning json (%s)', $templateName));
            }

            $commands = Json::decode($render);

            foreach ($commands as $command) {
                $this->io->section($command);
                $application->run(new StringInput($command), $output);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }
    }

    private function getTemplate(string $name): TemplateWrapper
    {
        $source = match (true) {
            u($name)->startsWith('@EMSCH') => $this->twig->getLoader()->getSourceContext($name)->getCode(),
            \file_exists($name) => File::fromFilename($name)->getContents(),
            default => $name
        };

        return $this->twig->createTemplate($source);
    }
}
