<?php

namespace App\Controller;

use App\Enum\Permission;
use App\Entity\CarCondition;
use App\Service\CarConditionService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CarConditionCrudController extends AbstractCrudController
{
    public function __construct(
        private CarConditionService $carConditionService
    ) {
    }

    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje CRUD w danym kontrolerze.
     * @return string FQCN encji obsługiwanej przez (`CarCondition::class`).
     */
    public static function getEntityFqcn(): string
    {
        return CarCondition::class;
    }

    /**
     * Konfiguracja podstawowych ustawień CRUD dla stanów pojazdów.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('carCondition.title')
            ->setEntityLabelInPlural('carCondition.title')
            ->setPageTitle('index', 'carCondition.list')
            ->setPageTitle('new', 'carCondition.add')
            ->setPageTitle('edit', 'carCondition.edit')
            ->setPageTitle('detail', 'carCondition.details');
    }

    /**
     * Konfiguracja pól formularza w panelu administratora  dla samochodów. Definiuje zestaw pól w zależności na której stronie jesteśmy, które dostępne są w formularzu tworzenia/edycji oraz w widokach listy i szczegółów. Metoda przygotowuje listy wyboru dla pól typu wyliczeniowego na podstawie ich wartości.
     * @param string $pageName Nazwa strony CRUD na podstawie której dobierana jest konfiguracja pól.
     * @return iterable Zwrócona lista pól przez serwis.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->carConditionService->configureFields($pageName);
    }

    /**
     * Przypisanie uprawnień i dodatkowych akcji widoku samochodów.
     * @param Actions $actions EasyAdmin konfiguruje obiekt.
     * @return Actions Zaktualizowany obiekt z poprawnymi uprawnieniami.
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
