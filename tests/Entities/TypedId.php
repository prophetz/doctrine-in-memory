<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory\Entities;

use Doctrine\ORM\Mapping;

/**
 * @Mapping\Entity
 * @Mapping\Table(name="typed_ids")
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'typed_ids')]
class TypedId
{
    /**
     * @Mapping\Id
     * @Mapping\Column
     * @Mapping\GeneratedValue
     */
    #[Mapping\Id]
    #[Mapping\Column]
    #[Mapping\GeneratedValue]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
