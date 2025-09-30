<?php

namespace App\Controller;

use App\Entity\Car;
use App\Enum\Permission;
use App\Service\CarService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CarCrudController extends AbstractCrudController
{
    public function __construct(
        private CarService $carService,
    ) {
    }

    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje CRUD w danym kontrolerze.
     * @return string FQCN encji obsługiwanej przez kontroler.
     */
    public static function getEntityFqcn(): string
    {
        return Car::class;
    }
    /**
     * Funkcja konfiguruje widok CRUD-a dla encji (Car) w EasyAdmin.
     * @param Crud $crud EasyAdmin udostepnia CRUD-a do konfiguracji .
     * @return Crud Zaktualizowany CRUD z ustawionym tytułem strony.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('car.title')
            ->setEntityLabelInPlural('car.title')
            ->setPageTitle(Crud::PAGE_INDEX, 'car.list')
            ->setPageTitle(Crud::PAGE_NEW, 'car.add')
            ->setPageTitle(Crud::PAGE_EDIT, 'car.edit')
            ->setPageTitle(Crud::PAGE_DETAIL, 'car.details');
    }

    /**
     * Konfiguracja pól formularza w panelu administratora  dla samochodów. Definiuje zestaw pól w zależności na której stronie jesteśmy, które dostępne są w formularzu tworzenia/edycji oraz w widokach listy i szczegółów. Metoda przygotowuje listy wyboru dla pól typu wyliczeniowego na podstawie ich wartości.
     * @param string $pageName Nazwa strony CRUD-a na podstawie której dobierana jest konfiguracja pól.
     * @return iterable Zwrócona lista pól przez serwis.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->carService->configureFields($pageName);
    }

    /**
     * Przypisanie uprawnień i dodatkowych akcji widoku samochodów.
     * @param Actions $actions Obiekt konfiguracji akcji EasyAdmin.
     * @return Actions Zaktualizowany obiekt konfiguracji z przypisanymi uprawnieniami.
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::INDEX, Permission::CAR_VIEW->value);
        $actions->setPermission(Action::DETAIL, Permission::CAR_VIEW->value);
        $actions->setPermission(Action::NEW, Permission::CAR_CREATE->value);
        $actions->setPermission(Action::EDIT, Permission::CAR_EDIT->value);
        $actions->setPermission(Action::DELETE, Permission::CAR_DELETE->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }
}
