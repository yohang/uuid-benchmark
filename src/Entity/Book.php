<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Uid\Uuid;

#[Entity]
class Book
{
    public int|Uuid $id;
}
