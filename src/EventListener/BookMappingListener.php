<?php

namespace App\EventListener;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
final readonly class BookMappingListener
{
    public const string ID_TYPE_INT = 'int';
    public const string ID_TYPE_UUID_V1 = 'uuid_v1';
    public const string ID_TYPE_UUID_V4 = 'uuid_v4';
    public const string ID_TYPE_UUID_V6 = 'uuid_v6';
    public const string ID_TYPE_UUID_V7 = 'uuid_v7';

    public const array VALID_ID_TYPES = [
        self::ID_TYPE_INT,
        self::ID_TYPE_UUID_V1,
        self::ID_TYPE_UUID_V4,
        self::ID_TYPE_UUID_V6,
        self::ID_TYPE_UUID_V7,
    ];

    public function __construct(#[Autowire('%env(ID_TYPE)%')] private string $idType)
    {
        if (!in_array($this->idType, self::VALID_ID_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid ID type "%s". Valid types are: %s', $this->idType, implode(', ', self::VALID_ID_TYPES)));
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        $builder = new ClassMetadataBuilder($metadata);
        $builder
            ->createField('id', $this->getDoctrineType())
            ->nullable(false)
            ->index(false)
            ->unique(false)
            ->makePrimaryKey()
            ->generatedValue($this->getGeneratorType())
            ->build();

        if (self::ID_TYPE_INT === $this->idType) {
            $metadata->setIdGenerator(new IdentityGenerator());
        }
    }

    private function getDoctrineType(): string
    {
        return match ($this->idType) {
            self::ID_TYPE_INT => Types::INTEGER,
            default => UuidType::NAME,
        };
    }

    private function getGeneratorType(): string
    {
        return match ($this->idType) {
            self::ID_TYPE_INT => 'IDENTITY',
            default => 'NONE',
        };
    }
}
