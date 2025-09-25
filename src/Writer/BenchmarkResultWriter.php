<?php

namespace App\Writer;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
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
        #[Autowire('%env(ID_TYPE)%')] private string                $idType,
        #[Autowire('%env(int:NUMBER_OF_ROOT_ENTITY)%')] private int $numberOfRootEntity,
        #[Autowire('%env(OUTPUT_CSV_FILE)%')] private string        $outputCsvFile,
        private Stopwatch                                           $stopwatch = new Stopwatch(),
        private Connection                                          $connection,
    )
    {
    }

    public function start(string $event): void
    {
        $this->stopwatch->start($event);
    }

    public function stop(string $event): void
    {
        $this->stopwatch->stop($event);
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        if (!$event->getCommand() instanceof LoadDataFixturesDoctrineCommand) {
            return;
        }

        $io = new SymfonyStyle(
            new StringInput(''),
            new StreamOutput(fopen('php://stdout', 'a'))
        );

        foreach ($this->stopwatch->getRootSectionEvents() as $event) {
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

        $this->writeCsv();
    }

    private function writeCsv(): void
    {
        $csvOutput = fopen($this->outputCsvFile, 'w');
        $events = $this->stopwatch->getRootSectionEvents();

        if (!$events) {
            return;
        }

        fputcsv(
            $csvOutput,
            [
                'ID Type',
                'Number of root entity',
                'Insert books time (ms)',
                'Insert books memory (MB)',
                'Insert reviews time (ms)',
                'Insert reviews memory (MB)',
                'Select books time (ms)',
                'Select books memory (MB)',
                'Select reviews time (ms)',
                'Select reviews memory (MB)',
                'Database size (MB)',
            ],
            escape: '',
        );

        $dbSize = $this->connection->executeQuery('SELECT pg_database_size(current_database()) AS db_size')->fetchOne();

        fputcsv(
            $csvOutput,
            [
                $this->idType,
                $this->numberOfRootEntity,
                $events['insert-books']->getDuration(),
                round($events['insert-books']->getMemory() / 1024 / 1024, 2),
                $events['insert-reviews']->getDuration(),
                round($events['insert-reviews']->getMemory() / 1024 / 1024, 2),
                $events['select-books']->getDuration(),
                round($events['select-books']->getMemory() / 1024 / 1024, 2),
                $events['select-reviews']->getDuration(),
                round($events['select-reviews']->getMemory() / 1024 / 1024, 2),
                round($dbSize / 1024 / 1024, 2),
            ],
            escape: '',
        );
    }
}
