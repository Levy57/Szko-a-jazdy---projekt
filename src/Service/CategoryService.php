<?php

declare(strict_types=1);

namespace App\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;

class CategoryService
{
    public function __construct()
    {
    }

    public function configureFields(string $pageName): iterable
    {
        yield  TextField::new('name', 'category.name');
        yield  ColorField::new('color', 'category.color');
    }
}
