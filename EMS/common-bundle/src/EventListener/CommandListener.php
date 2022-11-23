<?php

namespace EMS\CommonBundle\EventListener;

use EMS\CommonBundle\Command\CommandInterface;
use EMS\CommonBundle\Common\Converter;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class CommandListener implements EventSubscriberInterface
{
    private Stopwatch $stopwatch;

    public function __construct()
    {
        $this->stopwatch = new Stopwatch();
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand'],
            ConsoleEvents::TERMINATE => ['onTerminate'],
        ];
    }

    /**
     * @return void
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        if (!$command instanceof CommandInterface) {
            return;
        }

        $this->stopwatch->start((string) $command->getName());
    }

    /**
     * @return void
     */
    public function onTerminate(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();

        if (!$command instanceof CommandInterface) {
            return;
        }

        $stopwatch = $this->stopwatch->stop((string) $command->getName());

        $io = new SymfonyStyle($event->getInput(), $event->getOutput());
        $io->listing([
            \sprintf('Duration: %d s', $stopwatch->getDuration() / 1000),
            \sprintf('Memory: %s', Converter::formatBytes($stopwatch->getMemory())),
        ]);
    }
}
