<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Review;
use App\EventListener\BookMappingListener;
use App\Repository\ReviewRepository;
use App\Writer\BenchmarkResultWriter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

final class AppFixtures extends Fixture
{
    private const int BATCH_SIZE = 1_000;
    private const int NUM_SELECT = 10_000;

    public function __construct(
        #[Autowire('%env(ID_TYPE)%')] private readonly string                $idType,
        #[Autowire('%env(int:NUMBER_OF_ROOT_ENTITY)%')] private readonly int $numberOfRootEntity,
        private readonly BenchmarkResultWriter                               $resultWriter, private readonly ReviewRepository $reviewRepository,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManagerInterface $manager */

        for ($i = 1; $i <= 100; $i++) {
            $author = Author::create('Author ' . $i);
            $this->defineId($author);
            $manager->persist($author);

            $this->addReference('author_' . $i, $author);
        }
        $manager->flush();

        $this->resultWriter->start('insert-books');
        for ($i = 1; $i <= $this->numberOfRootEntity; $i++) {
            $book = Book::create($this->getReference('author_' . random_int(1, 100), Author::class), 'Book ' . $i);
            $this->defineId($book);

            $manager->persist($book);

            $this->addReference('book_' . $i, $book);

            if (0 === $i % self::BATCH_SIZE) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();

        $this->resultWriter->stop('insert-books');

        $this->resultWriter->start('insert-reviews');

        for ($i = 1; $i <= $this->numberOfRootEntity * 10; $i++) {
            $review = Review::create(
                $this->getReference('book_' . random_int(1, $this->numberOfRootEntity), Book::class),
                $this->getReference('author_' . random_int(1, 100), Author::class),
                'Review ' . $i
            );
            $this->defineId($review);

            $manager->persist($review);

            if (0 === $i % self::BATCH_SIZE) {
                $manager->flush();
                $manager->clear();
            }
        }

        $manager->flush();
        $manager->clear();
        $this->resultWriter->stop('insert-reviews');


        $id = $this->getReference('book_' . floor($this->numberOfRootEntity / 2), Book::class)->id;
        $this->resultWriter->start('select-books');
        for ($i = 0; $i < self::NUM_SELECT * 3; $i++) {
            $book = $manager->getRepository(Book::class)->find($id);
            assert($book->title); // Loads data from doctrine proxy

            $manager->clear();
        }
        $this->resultWriter->stop('select-books');


        $id = $this->reviewRepository->findOneBy(['review' => 'Review ' . ($this->numberOfRootEntity * 5)])->id;
        $this->resultWriter->start('select-reviews');
        for ($i = 0; $i < self::NUM_SELECT * 3; $i++) {
            $review = $manager->getRepository(Review::class)->find($id);
            assert($review->title); // Loads data from doctrine proxy

            $manager->clear();
        }
        $this->resultWriter->stop('select-reviews');
    }

    private function defineId(object $entity): void
    {
        if (BookMappingListener::ID_TYPE_INT !== $this->idType) {
            $entity->id = match ($this->idType) {
                BookMappingListener::ID_TYPE_UUID_V1 => Uuid::v1(),
                BookMappingListener::ID_TYPE_UUID_V4 => Uuid::v4(),
                BookMappingListener::ID_TYPE_UUID_V6 => Uuid::v6(),
                BookMappingListener::ID_TYPE_UUID_V7 => Uuid::v7(),
                default => throw new \InvalidArgumentException('Invalid ID_TYPE'),
            };
        }
    }
}
