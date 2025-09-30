<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Permission;
use App\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use App\Service\CustomerService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CustomerCrudController extends AbstractCrudController
{
    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje Crud w danym kontrolerze.
     * @return string FQCN encji obsługiwanej przez kontroler.
     */
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    public function __construct(
        private UserService $userService,
        private CustomerService $customerService,
    ) {}

    /**
     * Konfigurowanie parametrów na panelu administratora w zależności od strony.
     * @param KeyValueStore $responseParameters Parametry przekazywane do widoku przez EasyAdmin.
     * @return KeyValueStore Zaktualizowany zestaw parametrów lub niezmieniony.
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $pageName = $responseParameters->get('pageName');

        return match ($pageName) {
            Crud::PAGE_DETAIL => $this->customerService->pageDetail($responseParameters),
            default => $responseParameters,
        };
    }
    /**
     * Konfiguracja ustawień widoku CRUD dla encji klienta w panelu admina. Tytuły stron są nadpisywane domyślnie szablonem DETAIL dla wybranych widoków własnym plikiem Twig.
     * @param Crud $crud Instancja konfiguratora EasyAdmin.
     * @return Crud Skonfigurowany CRUD.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('customer')
            ->setEntityLabelInPlural('customers')
            ->setPageTitle(Crud::PAGE_INDEX, 'customer.list')
            ->setPageTitle(Crud::PAGE_DETAIL, 'customer.details')
            ->setPageTitle(Crud::PAGE_NEW, 'customer.addCustomer')
            ->setPageTitle(Crud::PAGE_EDIT, 'customer.edit')
            ->overrideTemplate('crud/detail', 'customer/index.html.twig');
    }
    /**
     * Konfiguracja pola formularza i widoku Crud dla encji użytkownika w panelu admina. Definiuje zestaw pól wyświetlanych w formularzu oraz w widokach listy i szczegółów. Funkcja przygotowuje listę dostepnych kategorii.
     * @param string $pageName Nazwa aktualnej strony CRUD.
     * @return iterable <\EasyCorp\Bundle\EasyAdminBundle\Field\FieldInterface> Lista pól do wyświetlenia/edytowania.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->customerService->configureFields($pageName);
    }
    /**
     * Zapisywanie do bazy danych instancje encji przy użyciu serwisu `UserService`. Funkcja przy zpisie encji, deleguje logikę danych do userService, dzieki temu można roszerzać proces zapisu w jednym miejscu.
     * @param EntityManagerInterface $entityManager Menedżer encji Doctrine
     * @param object $entityInstance Nowa instancja encji do zapisania (User)
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userService->persistEntity($entityManager, $entityInstance);
    }
    /**
     * Przy użyciu UserService aktualizuje istniejącą encje w bazie danych. Metoda nadpisuje domyślne zachowanie EasyAdmin dla operacji aktualizacji.
     * @param EntityManagerInterface $entityManager Obiekt odpowiedzialny za komunikację z bazą danych.
     * @param object $entityInstance Encja, która została zmodyfikowana i powinna zostać zaktualizowana.
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userService->updateEntity($entityManager, $entityInstance);
    }
    /**
     * QueryBuilder tworzy zapytanie dla widoku listy encji z dodatkowym filtrowaniem po uprawnieniach.
     * @param SearchDto $searchDto Obiekt zawierający parametry wyszukiwania w EasyAdmin.
     * @param EntityDto $entityDto Obiekt opisujący encje CRUD-a.
     * @param FieldCollection $fields Kolekcja pól do uwzględnienia w zapytaniu.
     * @param FilterCollection $filters Kolekcja filtrów stosowanych w widoku listy.
     * @return QueryBuilder Obiekt Doctrine QueryBuilder przygotowany do pobrania encji.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->join('entity.roles', 'role')
            ->andWhere('JSON_CONTAINS(role.permissions, :permission) = 1')
            ->setParameter('permission', '"' . Permission::CUSTOMER->value . '"');

        return $qb;
    }
    /**
     * Przypisanie uprawnień do akcji panelu admina.
     * @param Actions $actions Obiekt konfiguracji akcji EasyAdmin.
     * @return Actions Zaktualizowany obiekt konfiguracji z uprawnieniami i akcją szczegółów.
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::INDEX, Permission::CUSTOMER_VIEW->value);
        $actions->setPermission(Action::DETAIL, Permission::CUSTOMER_DETAIL->value);
        $actions->setPermission(Action::NEW, Permission::CUSTOMER_CREATE->value);
        $actions->setPermission(Action::EDIT, Permission::CUSTOMER_EDIT->value);
        $actions->setPermission(Action::DELETE, Permission::CUSTOMER_DELETE->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }
}
