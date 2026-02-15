<?php

declare(strict_types=1);

namespace EMS\CommonBundle\Common\Command;

use EMS\CommonBundle\Command\CommandInterface;
use EMS\CommonBundle\Common\EMSLink;
use EMS\Helpers\Standard\DateTime;
use EMS\Helpers\Standard\Type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

abstract class AbstractCommand extends Command implements CommandInterface
{
    protected SymfonyStyle $io;
    protected InputInterface $input;
    protected OutputInterface $output;
    protected ProcessHelper $processHelper;

    #[\Override]
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->input = $input;
        $this->output = $output;
        /** @var ProcessHelper $processHelper */
        $processHelper = $this->getHelper('process');
        $this->processHelper = $processHelper;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return self::EXECUTE_SUCCESS;
    }

    /**
     * @param array<mixed> $choices
     *
     * @return mixed
     */
    protected function askChoiceQuestion(string $question, array $choices, bool $multiple = false)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion($question, $choices);
        $question->setMultiselect($multiple);
        $question->setErrorMessage('Choice %s is invalid.');

        return $helper->ask($this->input, $this->output, $question);
    }

    protected function getArgumentBool(string $name): bool
    {
        if (null === $arg = $this->input->getArgument($name)) {
            throw new \RuntimeException(\sprintf('Missing argument "%s"', $name));
        }

        return (bool) $arg;
    }

    protected function getArgumentString(string $name, ?string $question = null): string
    {
        $arg = $this->input->getArgument($name);
        if (null === $arg && null !== $question) {
            $arg = $this->io->ask(
                question: $question,
                validator: function ($value) use ($name) {
                    if (null === $value || '' === $value) {
                        throw new \RuntimeException(\sprintf('Argument %s must be defined.', $name));
                    }

                    return Type::string($value);
                }
            );
        } elseif (null === $arg) {
            throw new \RuntimeException(\sprintf('Missing argument "%s"', $name));
        }

        return (string) $arg;
    }

    protected function getArgumentDateTime(string $name): \DateTimeInterface
    {
        $arg = $this->getArgumentString($name);

        return DateTime::create($arg);
    }

    protected function getArgumentStringNull(string $name): ?string
    {
        if (null === $arg = $this->input->getArgument($name)) {
            return null;
        }

        return (string) $arg;
    }

    /**
     * @return string[]
     */
    protected function getArgumentStringArray(string $name): array
    {
        $arg = $this->input->getArgument($name);
        if (!\is_array($arg) || [] === $arg) {
            throw new \RuntimeException(\sprintf('Missing array argument "%s"', $name));
        }

        return $arg;
    }

    /**
     * @return string[]
     */
    protected function getArgumentOptionalStringArray(string $name): array
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param string[] $choices
     */
    protected function choiceArgumentArray(string $name, string $question, array $choices): void
    {
        $argument = $this->input->getArgument($name);

        if (\in_array('all', $argument)) {
            $this->input->setArgument($name, $choices);

            return;
        }

        if ((\is_countable($argument) ? \count($argument) : 0) > 0) {
            return;
        }

        $allChoices = ['all', ...$choices];
        $answer = $this->askChoiceQuestion($question, $allChoices, true);

        $argument = \in_array('all', $answer) ? $choices : $answer;

        $this->input->setArgument($name, $argument);
    }

    /**
     * @param string[] $choices
     */
    protected function choiceArgumentString(string $name, string $question, array $choices): void
    {
        if (null !== $this->input->getArgument($name)) {
            return;
        }

        $this->input->setArgument($name, $this->askChoiceQuestion($question, $choices));
    }

    protected function getArgumentInt(string $name): int
    {
        if (null === $arg = $this->input->getArgument($name)) {
            throw new \RuntimeException(\sprintf('Missing argument "%s"', $name));
        }

        return (int) $arg;
    }

    /**
     * @return int[]
     */
    protected function getArgumentIntArray(string $name): array
    {
        return \array_map(\intval(...), $this->getArgumentStringArray($name));
    }

    protected function getOptionBool(string $name): bool
    {
        return true === $this->input->getOption($name);
    }

    protected function getOptionInt(string $name, ?int $default = null): int
    {
        if (null !== $option = $this->input->getOption($name)) {
            return (int) $option;
        }

        if (null === $default) {
            throw new \RuntimeException(\sprintf('Missing option "%s"', $name));
        }

        return $default;
    }

    protected function getOptionFloat(string $name, ?float $default = null): float
    {
        if (null !== $option = $this->input->getOption($name)) {
            return (float) $option;
        }

        if (null === $default) {
            throw new \RuntimeException(\sprintf('Missing option "%s"', $name));
        }

        return $default;
    }

    /**
     * @return int[]
     */
    protected function getOptionIntArray(string $name): array
    {
        return \array_map(\intval(...), $this->getOptionStringArray($name));
    }

    protected function getOptionIntNull(string $name): ?int
    {
        $option = $this->input->getOption($name);

        return null === $option ? null : (int) $option;
    }

    protected function getOptionString(string $name, ?string $default = null): string
    {
        if (null !== $option = $this->input->getOption($name)) {
            return (string) $option;
        }

        if (null === $default) {
            throw new \RuntimeException(\sprintf('Missing option "%s"', $name));
        }

        return $default;
    }

    /**
     * @return string[]
     */
    protected function getOptionStringArray(string $name, bool $required = true): array
    {
        $option = $this->input->getOption($name);
        if ($required && (!\is_array($option) || [] === $option)) {
            throw new \RuntimeException(\sprintf('Missing array option "%s"', $name));
        }

        return \is_array($option) ? \array_map(fn ($v) => (string) $v, $option) : [];
    }

    protected function getOptionStringNull(string $name): ?string
    {
        $option = $this->input->getOption($name);

        return null === $option ? null : (string) $option;
    }

    protected function getArgumentEmsLink(string $name): EMSLink
    {
        $argument = $this->input->getArgument($name);

        return EMSLink::fromText(Type::string($argument));
    }

    protected function getArgumentEmsLinkNull(string $name): ?EMSLink
    {
        $argument = $this->input->getArgument($name);

        return null === $argument ? null : EMSLink::fromText(Type::string($argument));
    }

    protected function getOptionEmsLink(string $name): EMSLink
    {
        $option = $this->input->getOption($name);

        return EMSLink::fromText(Type::string($option));
    }

    protected function getOptionEmsLinkNull(string $name): ?EMSLink
    {
        $option = $this->input->getOption($name);

        return null === $option ? null : EMSLink::fromText(Type::string($option));
    }

    protected function getOptionDateTime(string $name): \DateTimeImmutable
    {
        return DateTime::create(Type::string($this->input->getOption($name)));
    }

    protected function getOptionDateTimeNull(string $name): ?\DateTimeImmutable
    {
        $option = $this->input->getOption($name);

        return null === $option ? null : DateTime::create(Type::string($option));
    }

    protected function getArgumentDateTimeNull(string $name): ?\DateTimeImmutable
    {
        $option = $this->input->getArgument($name);

        return null === $option ? null : DateTime::create(Type::string($option));
    }

    /**
     * Execute command in real php sub process.
     *
     * @param string[] $args
     */
    protected function executeCommand(string $command, array $args): int
    {
        $emsProcessCommand = $_SERVER['EMS_PROCESS_COMMAND'] ?? 'php bin/console';
        $processCommand = \array_merge(\explode(' ', (string) $emsProcessCommand), [$command, ...$args]);

        $process = new Process($processCommand);
        $process->setTimeout(null);
        $process->setIdleTimeout(null);
        $this->io->write(\implode(' ', [$command, ...$args]).': ');

        $this->processHelper->run($this->output, $process, 'Something went wrong!', function () {
            $this->io->write('*');
        });

        if ($process->isSuccessful()) {
            $this->io->write(' <fg=green>SUCCESS</>');
            $this->io->newLine();

            return 0;
        }

        throw new \RuntimeException($process->getErrorOutput());
    }

    /**
     * Run command in same php process.
     *
     * @param array<string, mixed> $args
     * @param array<string, mixed> $options
     */
    protected function runCommand(string $command, array $args = [], array $options = []): int
    {
        if (null === $application = $this->getApplication()) {
            throw new \RuntimeException('could not find application');
        }

        $input = ['command' => $command, ...$args];
        foreach ($options as $name => $value) {
            $input['--'.$name] = $value;
        }

        return $application->doRun(new ArrayInput($input), $this->output);
    }
}
