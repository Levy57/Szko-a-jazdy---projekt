<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Enum\Permission;
use App\Service\FaqService;
use App\Repository\FaqRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FaqCrudController extends AbstractCrudController
{
    public function __construct(
        private FaqRepository $faqRepository,
        private FaqService $faqService,
    ) {}

    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia na jakiej encji będą wykonywane operacje crud-a w danym kontrolerze.
     * @return string FQCN encji obsługiwanej przez ten kontroler (`Faq::class`).
     */
    public static function getEntityFqcn(): string
    {
        return Faq::class;
    }
    /**
     * Funkcja konfiguruje ustawienia widoku. Ustawia tytuł strony „FAQ”
     * @param Crud $crud Obiekt konfiguracji CRUD udostępniany przez EasyAdmin.
     * @return Crud Skonfigurowany obiekt CRUD z ustawionym tytułem strony i szablonem.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'FAQ')
            ->overrideTemplate('crud/index', 'faq.html.twig');
    }
    /**
     * Funkcja konfiguruje parametry przekazywane doFAQ w EasyAdmin.
     * @param KeyValueStore $responseParameters Parametry przekazywane do widoku przez EasyAdmin.
     * @return KeyValueStore Zaktualizowany zestaw parametrów z listą FAQ lub niezmieniony.
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if (Crud::PAGE_INDEX === $responseParameters->get('pageName')) {
            $faq = $this->faqRepository->findAll();
            $responseParameters->set('faq', $faq);
        }

        return $responseParameters;
    }
    /**
     * Funkcja definiuje pola formularza CRUD dla encji FAQ w panelu admina. Metoda zwraca listę pól, które mają być wyświetlane lub edytowane.
     * @param string $pageName Nazwa strony.
     * @return iterable Kolekcja skonfigurowanych pól do wyświetlenia na podanej stronie.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->faqService->configureFields($pageName);
    }
    /**
     * Funkcja konfiguruje dostepne akcje w panelu EasyAdmin dla encji FAQ, ustawia uprawnienia umożliwiające szybki dostęp bezpośrednio do listy.
     * @param Actions $actions Obiekt konfiguracji akcji EasyAdmin.
     * @return Actions Zaktualizowana konfiguracja akcji z nałożonymi uprawnieniami i dodatkowymi opcjami.
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, Permission::FAQ_MANAGMENT->value);
        $actions->setPermission(Action::EDIT, Permission::FAQ_MANAGMENT->value);
        $actions->setPermission(Action::NEW, Permission::FAQ_MANAGMENT->value);
        $actions->setPermission(Action::DETAIL, Permission::FAQ_MANAGMENT->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        return $actions;
    }
}
