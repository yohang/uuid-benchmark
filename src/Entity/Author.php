<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;

#[Entity]
class Author
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private(set) int $id;

    #[Column]
    private(set) string $name;

    #[OneToMany(targetEntity: Review::class, mappedBy: 'author')]
    private(set) Collection $reviews;

    private function __construct()
    {
        $this->reviews = new ArrayCollection;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function create(string $name): self
    {
        $author = new self();
        $author->name = $name;

        return $author;
    }
}
