<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory\Entities;

use Doctrine\ORM\Mapping;

/**
 * @Mapping\Entity
 * @Mapping\Table(name="string_ids")
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'string_ids')]
class StringId
{
    /**
     * @Mapping\Id
     * @Mapping\Column(type="string")
     * @Mapping\GeneratedValue
     * @var ?string
     */
    #[Mapping\Id]
    #[Mapping\Column]
    #[Mapping\GeneratedValue]
    private $id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}
