<?php

declare(strict_types=1);

namespace App\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CarService
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
        yield TextField::new('brand', 'car.brand');
        yield IntegerField::new('year', 'car.year')
            ->hideOnIndex();
        yield TextField::new('title', 'name');
        yield TextField::new('vin', 'car.vin')
            ->hideOnIndex();
        yield AssociationField::new('condition', 'car.condition')
            ->autocomplete();
        yield AssociationField::new('categories', 'car.categories')
            ->autocomplete(); //TODO SHOW CATEGORIES NAME INSTEAD NUMBER OF RELATIONS
        yield TextareaField::new('note', 'car.note');
    }
}
