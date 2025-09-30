<?php

declare(strict_types=1);

namespace App\Service;

use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class FaqService
{
    public function __construct() {}

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'faq.title');
        yield TextEditorField::new('description', 'faq.content');
    }
}
