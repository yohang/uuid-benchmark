<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Uid\Uuid;

#[Entity]
class Book
{
    public int|Uuid $id;

    #[ManyToOne(targetEntity: Author::class)]
    private(set) Author $author;

    #[Column(type: Types::STRING)]
    private(set) string $title;

    private function __construct()
    {
    }

    public static function create(Author $author, string $title): self
    {
        $book = new self();
        $book->author = $author;
        $book->title = $title;

        return $book;
    }
}
