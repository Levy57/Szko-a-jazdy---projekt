<?php

namespace App\DataFixtures;

use App\Entity\CarCondition;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CarConditionFixures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['sprawny', 'niesprawny', 'naprawa'];
        $colors = ['#4a9e26', '#d91111', '#e3b409'];

        foreach ($categories as $key => $cat) {
            $carCondition = new CarCondition();
            $carCondition->setName($cat);
            $carCondition->setColor($colors[$key]);
            $manager->persist($carCondition);
        }

        $manager->flush();
    }
}
