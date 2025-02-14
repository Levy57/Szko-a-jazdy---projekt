<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Enum\Role;
use App\Repository\FaqRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FaqCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }

    public function __construct(
        private FaqRepository $faqRepository
    ) {
        $this->faqRepository = $faqRepository;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Tytuł'),
            TextareaField::new('tekst', "Treść"),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'FAQ')
            ->overrideTemplate('crud/index', 'faq.html.twig');
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_INDEX) {
            $faq = $this->faqRepository->findAll();
            $responseParameters->set('faq', $faq);
        }

        return $responseParameters;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::EDIT, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::NEW, (Role::ROLE_ADMIN)->value);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->setPermission(Action::DETAIL, (Role::ROLE_ADMIN)->value);

        return $actions;
    }

    public function detail(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::detail($context);
    }
    public function new(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::new($context);
    }
    public function edit(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::edit($context);
    }
    public function delete(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::delete($context);
    }
}
