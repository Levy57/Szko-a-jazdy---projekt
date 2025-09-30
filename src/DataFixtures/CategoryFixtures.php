<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = ['AM', 'A1', 'A2', 'A', 'B1', 'B', 'C1', 'C', 'D1', 'D', 'BE', 'C1E', 'CE', 'D1E', 'DE', 'T'];
        $color = ['#B8860B', '#1E4D7C', '#1C4B6D', '#003366', '#2E8B57', '#006400', '#D35D33', '#8B0000', '#B45308', '#B22222', '#6A006A', '#8B3E2F', '#A0522D', '#7A3F24', '#2F4F4F', '#6A8E23'];

        foreach ($categories as $key => $cat) {
            $category = new Category();
            $category->setName($cat);
            $category->setColor($color[$key]);
            $manager->persist($category);
        }

        $manager->flush();
    }
}
