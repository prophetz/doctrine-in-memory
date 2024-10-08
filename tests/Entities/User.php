<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping;

/**
 * @Mapping\Entity
 * @Mapping\Table(name="users")
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'users')]
class User
{
    /**
     * @Mapping\Id
     * @Mapping\Column(type="integer")
     * @Mapping\GeneratedValue
     * @var ?int
     */
    #[Mapping\Id]
    #[Mapping\Column(type: Types::INTEGER)]
    #[Mapping\GeneratedValue]
    private $id;

    /**
     * @Mapping\Column
     * @var string
     */
    #[Mapping\Column]
    private $email;

    /**
     * @Mapping\Column(type="boolean")
     * @var bool
     */
    #[Mapping\Column(type: Types::BOOLEAN)]
    private $active = false;

    /**
     * @Mapping\Column(name="last_name")
     * @var string
     */
    #[Mapping\Column]
    private $lastName;

    /**
     * @var int
     */
    private $notAColumn = 100;

    public function __construct(
        string $email,
        string $lastName,
        ?int $id = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->lastName = $lastName;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getNotAColumn(): int
    {
        return $this->notAColumn;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setNotAColumn(int $notAColumn): void
    {
        $this->notAColumn = $notAColumn;
    }
}
