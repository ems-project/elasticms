<?php

declare(strict_types=1);

namespace EMS\SubmissionBundle\Command;

use EMS\SubmissionBundle\Repository\FormSubmissionRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Address;

final class DatabaseStatsCommand extends Command
{
    protected static $defaultName = 'emss:database:stats';

    public function __construct(private readonly Mailer $mailer, private readonly FormSubmissionRepository $repository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('formName', InputArgument::REQUIRED)
            ->addOption('instance', null, InputOption::VALUE_REQUIRED, 'instance')
            ->addOption('period', null, InputOption::VALUE_REQUIRED, 'period', '1 day')
            ->addOption('email-to', null, InputOption::VALUE_REQUIRED, 'to emails (comma separated)')
            ->addOption('email-subject', null, InputOption::VALUE_REQUIRED, 'subject', 'submission stats')
            ->addOption('email-from', null, InputOption::VALUE_REQUIRED, 'from email', 'noreply@elasticms.eu')
            ->addOption('email-from-name', null, InputOption::VALUE_REQUIRED, 'from name', 'elasticms form submissions')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);
        $style->title('EMS Submission: database stats');

        /** @var string $formName */
        $formName = $input->getArgument('formName');
        /** @var string $instance */
        $instance = $input->getOption('instance');
        /** @var string $period */
        $period = $input->getOption('period');
        /** @var string|null $emailTo */
        $emailTo = $input->getOption('email-to');

        $counts = $this->repository->getCounts($formName, $period, $instance);
        $style->table(
            ['type', 'value'],
            [...$counts->toArrayPeriod(), ...[new TableSeparator()], ...$counts->toArray()]
        );

        if (null !== $emailTo) {
            $message = $this->createMessage($input);
            $message
                ->htmlTemplate('@EMSSubmission/mail/stats.html.twig')
                ->context([
                    'formName' => $formName,
                    'count' => $counts,
                    'from' => $input->getOption('email-from-name'),
                ]);

            $this->mailer->send($message);

            $style->success(\sprintf('Send stats email to: %s', $emailTo));
        }

        return 1;
    }

    private function createMessage(InputInterface $input): TemplatedEmail
    {
        /** @var string $emailTo */
        $emailTo = $input->getOption('email-to');
        $toEmail = \explode(',', $emailTo);

        /** @var string $subject */
        $subject = $input->getOption('email-subject');
        /** @var string $fromEmail */
        $fromEmail = $input->getOption('email-from');
        /** @var string $fromName */
        $fromName = $input->getOption('email-from-name');

        return (new TemplatedEmail())
            ->subject($subject)
            ->from(new Address($fromEmail, $fromName))
            ->to(...$toEmail);
    }
}
