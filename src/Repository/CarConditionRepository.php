<?php

namespace App\Repository;

use App\Entity\CarCondition;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<CarCondition>
 */
class CarConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarCondition::class);
    }

    public function getArray()
    {
        $conditions = [];
        foreach ($this->findAll() as $c) {
            $conditions[$c->getName()] = $c;
        }
        return $conditions;
    }
}
