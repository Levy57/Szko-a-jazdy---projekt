<?php

declare(strict_types=1);

namespace App\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;

class CarConditionService
{
    public function __construct()
    {
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'name');
        yield ColorField::new('color', 'color');
    }
}
