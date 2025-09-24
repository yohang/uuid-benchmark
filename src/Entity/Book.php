<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: BookRepository::class)]
class Book
{
    public int|Uuid $id;

    #[ManyToOne(targetEntity: Author::class)]
    private(set) Author $author;

    #[Column(type: Types::STRING)]
    private(set) string $title;

    /**
     * @var Collection<int, Review>
     */
    #[OneToMany(targetEntity: Review::class, mappedBy: 'book')]
    private(set) Collection $reviews;

    private function __construct()
    {
        $this->reviews = new ArrayCollection;
    }

    public static function create(Author $author, string $title): self
    {
        $book = new self();
        $book->author = $author;
        $book->title = $title;

        return $book;
    }
}
