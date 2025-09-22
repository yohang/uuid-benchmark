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

    private function __construct()
    {
    }

    public static function create(Book $book): self
    {
        $review = new self();
        $review->book = $book;

        return $review;
    }
}
