<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\EventListener\BookMappingListener;
use App\Writer\BenchmarkResultWriter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

final class AppFixtures extends Fixture
{
    private const int BATCH_SIZE = 1_000;

    public function __construct(
        #[Autowire('%env(ID_TYPE)%')] private readonly string $idType,
        #[Autowire('%env(int:NUMBER_OF_ROOT_ENTITY)%')] private readonly int $numberOfRootEntity,
        private readonly BenchmarkResultWriter $resultWriter,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->resultWriter->start('insert-books');
        for ($i = 1; $i <= $this->numberOfRootEntity; $i++) {
            $book = new Book;
            if (BookMappingListener::ID_TYPE_INT !== $this->idType) {
                $book->id = match ($this->idType) {
                    BookMappingListener::ID_TYPE_UUID_V1 => Uuid::v1(),
                    BookMappingListener::ID_TYPE_UUID_V4 => Uuid::v4(),
                    BookMappingListener::ID_TYPE_UUID_V6 => Uuid::v6(),
                    BookMappingListener::ID_TYPE_UUID_V7 => Uuid::v7(),
                    default => throw new \InvalidArgumentException('Invalid ID_TYPE'),
                };
            }

            $manager->persist($book);

            if (0 === $i % self::BATCH_SIZE) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();

        $this->resultWriter->stop('insert-books');
    }
}
