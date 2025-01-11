<?php

declare(strict_types=1);

namespace EMS\ClientHelperBundle\Command\Local;

use EMS\CommonBundle\Contracts\CoreApi\Exception\NotAuthenticatedExceptionInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

final class LoginCommand extends AbstractLocalCommand
{
    private const string ARG_USERNAME = 'username';
    private const string ARG_PASSWORD = 'password';

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this
            ->addArgument(self::ARG_USERNAME, InputArgument::OPTIONAL, 'username')
            ->addArgument(self::ARG_PASSWORD, InputArgument::OPTIONAL, 'password')
        ;
    }

    #[\Override]
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getArgument(self::ARG_USERNAME)) {
            $input->setArgument(self::ARG_USERNAME, $this->io->askQuestion(new Question('Username')));
        }

        if (null === $input->getArgument(self::ARG_PASSWORD)) {
            $input->setArgument(self::ARG_PASSWORD, $this->io->askHidden('Password'));
        }
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Local development - login');

        try {
            $coreApi = $this->localHelper->login(
                $this->environment,
                $this->getArgumentString(self::ARG_USERNAME),
                $this->getArgumentString(self::ARG_PASSWORD)
            );
        } catch (NotAuthenticatedExceptionInterface) {
            $this->io->error('Invalid credentials!');

            return self::EXECUTE_ERROR;
        } catch (\Throwable $e) {
            $this->io->error($e->getMessage());

            return self::EXECUTE_ERROR;
        }

        $profile = $coreApi->user()->getProfileAuthenticated();
        $this->io->success(\sprintf('Welcome %s on %s', $profile->getUsername(), $this->environment->getBackendUrl()));

        return self::EXECUTE_SUCCESS;
    }
}
