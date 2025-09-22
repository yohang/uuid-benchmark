<?php

namespace App\Writer;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Stopwatch\Stopwatch;

#[AsEventListener(event: ConsoleEvents::TERMINATE, method: 'onConsoleTerminate')]
final readonly class BenchmarkResultWriter
{
    public function __construct(
        #[Autowire('%env(ID_TYPE)%')] private string $idType,
        private Stopwatch $stopwatch = new Stopwatch(),
    )
    {
    }

    public function start(string $event): void
    {
        if (in_array('benchmark', $this->stopwatch->getSections())) {
            $this->stopwatch->openSection('benchmark');
        } else {
            $this->stopwatch->openSection();
        }

        $this->stopwatch->start($event);
    }

    public function stop(string $event): void
    {
        $this->stopwatch->stop($event);
        $this->stopwatch->stopSection('benchmark');
    }

    public function onConsoleTerminate(): void
    {
        $io = new SymfonyStyle(
            new StringInput(''),
            new StreamOutput(fopen('php://stdout', 'a'))
        );

        foreach ($this->stopwatch->getSectionEvents('benchmark') as $event) {
            if ('__section__' === $event->getName() || '__section__.child' === $event->getName()) {
                continue;
            }

            $io->section($event->getName());
            $io->table(
                ['ID Type', 'Duration (ms)', 'Memory (MB)'],
                [[
                    $this->idType,
                    $event->getDuration(),
                    round($event->getMemory() / 1024 / 1024, 2),
                ]]
            );
        }
    }
}
