<?php

declare(strict_types=1);

namespace Prophetz\DoctrineInMemory\Entities;

use Doctrine\ORM\Mapping;

/**
 * @Mapping\Entity
 * @Mapping\Table(name="groups")
 */
#[Mapping\Entity]
#[Mapping\Table(name: 'groups')]
class Group
{
}
