<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    public int|Uuid $id;

    #[ManyToOne(targetEntity: Book::class, inversedBy: 'reviews')]
    private(set) Book $book;

    #[ManyToOne(targetEntity: Author::class, inversedBy: 'reviews')]
    private(set) Author $author;

    #[Column(type: Types::TEXT)]
    private(set) string $review;

    private function __construct()
    {
    }

    public static function create(Book $book, Author $author, string $reviewText): self
    {
        $review = new self();
        $review->book = $book;
        $review->author = $author;
        $review->review = $reviewText;

        return $review;
    }
}
