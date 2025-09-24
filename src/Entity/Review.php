<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity]
class Review
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private(set) int $id;

    #[ManyToOne(targetEntity: Book::class)]
    private(set) Book $book;

    #[ManyToOne(targetEntity: Author::class)]
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
