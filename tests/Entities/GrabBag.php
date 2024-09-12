<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory\Entities;

use DateTimeInterface;
use Doctrine\ORM\Mapping;

/**
 * @Mapping\Entity
 * @Mapping\Table(name="grab_bags")
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'grab_bags')]
class GrabBag
{
    /**
     * @Mapping\Id
     * @Mapping\Column
     */
    #[Mapping\Id]
    #[Mapping\Column]
    private int $id;

    /**
     * @Mapping\Column(name="bool_field", type="boolean")
     */
    #[Mapping\Column]
    private bool $boolField;

    /**
     * @Mapping\Column(name="float_field", type="float")
     */
    #[Mapping\Column]
    private float $floatField;

    /**
     * @Mapping\Column(name="str_field")
     */
    #[Mapping\Column]
    private string $strField;

    /**
     * @Mapping\Column(name="date_field", type="date")
     */
    #[Mapping\Column]
    private DateTimeInterface $dateField;

    public function __construct(
        bool $boolField,
        float $floatField,
        string $strField,
        DateTimeInterface $dateField
    ) {
        $this->boolField = $boolField;
        $this->floatField = $floatField;
        $this->strField = $strField;
        $this->dateField = $dateField;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function isBoolField(): bool
    {
        return $this->boolField;
    }

    public function setBoolField(bool $boolField): void
    {
        $this->boolField = $boolField;
    }

    public function getFloatField(): float
    {
        return $this->floatField;
    }

    public function setFloatField(float $floatField): void
    {
        $this->floatField = $floatField;
    }

    public function getStrField(): string
    {
        return $this->strField;
    }

    public function setStrField(string $strField): void
    {
        $this->strField = $strField;
    }

    public function getDateField(): DateTimeInterface
    {
        return $this->dateField;
    }

    public function setDateField(DateTimeInterface $dateField): void
    {
        $this->dateField = $dateField;
    }
}
