<?php

namespace App\Controller;

use App\Entity\Category;
use App\Enum\Permission;
use App\Service\CategoryService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CategoryCrudController extends AbstractCrudController
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje CRUD w danym kontrolerze.
     * @return string Pełna nazwa klasy encji.
     */
    public static function getEntityFqcn(): string
    {
        return Category::class;
    }

    /**
     * Konfiguracja podstawowych ustawień CRUD dla kategorii.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('category.title')
            ->setEntityLabelInPlural('category.title')
            ->setPageTitle(Crud::PAGE_INDEX, 'category.list')
            ->setPageTitle(Crud::PAGE_NEW, 'category.add')
            ->setPageTitle(Crud::PAGE_EDIT, 'category.edit')
            ->setPageTitle(Crud::PAGE_DETAIL, 'category.details');
    }

    /**
     * Konfiguracja pól formularza w panelu administratora  dla samochodów. Definiuje zestaw pól w zależności na której stronie jesteśmy, które dostępne są w formularzu tworzenia/edycji oraz w widokach listy i szczegółów. Metoda przygotowuje listy wyboru dla pól typu wyliczeniowego na podstawie ich wartości.
     * @param string $pageName Nazwa strony.
     * @return iterable Lista skonfigurowanych pól dla danej strony.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->categoryService->configureFields($pageName);
    }

    /**
     * Przypisanie uprawnień i dodatkowych akcji widoku samochodów.
     * @param Actions $actions Obiekt konfiguracji akcji EasyAdmin.
     * @return Actions Zaktualizowana konfiguracja akcji z uprawnieniami.
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::INDEX, Permission::ADMIN->value);
        $actions->setPermission(Action::DETAIL, Permission::ADMIN->value);
        $actions->setPermission(Action::NEW, Permission::ADMIN->value);
        $actions->setPermission(Action::EDIT, Permission::ADMIN->value);
        $actions->setPermission(Action::DELETE, Permission::ADMIN->value);

        return $actions;
    }
}
