<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;

#[Entity]
class Author
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: Types::INTEGER)]
    private(set) int $id;

    #[Column]
    private(set) string $name;

    private function __construct()
    {
    }

    public static function create(string $name): self
    {
        $author = new self();
        $author->name = $name;

        return $author;
    }
}
