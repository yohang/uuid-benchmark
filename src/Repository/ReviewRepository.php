<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * @return array<
     *     int,
     *     array{0: Review, 1: int}
     * >
     */
    public function getBookReviewsWithAuthorAndReviewCount(Book $book): array
    {
        return $this->createQueryBuilder('r')
            ->select('r AS review')
            ->addSelect('author')
            ->addSelect('COUNT(reviews.id) AS author_review_count')
            ->innerJoin('r.author', 'author')
            ->innerJoin('author.reviews', 'reviews')
            ->where('r.book = :book')
            ->setParameter('book', $book)
            ->groupBy('r', 'author')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<
     *     int|Uuid
     *     array<
     *         int,
     *         array{0: Review, 1: int},
     *     >
     * >
     */
    public function getBooksReviewsWithAuthorAndReviewCount(array $books): array
    {
        return array_reduce(
            $this->createQueryBuilder('r')
                ->select('r AS review')
                ->addSelect('author')
                ->addSelect('COUNT(reviews.id) AS author_review_count')
                ->innerJoin('r.book', 'book')
                ->innerJoin('r.author', 'author')
                ->innerJoin('author.reviews', 'reviews')
                ->where('r.book IN (:books)')
                ->groupBy('r', 'author')
                ->setParameter('books', $books)
                ->getQuery()
                ->getResult(),
            function (array $carry, array $row) {
                $carry[(string)$row['review']->book->id] = [
                    ...($carry[$row['review']->book->id] ?? []),
                    $row,
                ];

                return $carry;
            },
            [],
        );
    }
}
