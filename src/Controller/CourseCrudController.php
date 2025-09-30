<?php

namespace App\Controller;

use App\Entity\Course;
use App\Enum\Permission;
use Doctrine\ORM\QueryBuilder;
use App\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CourseCrudController extends AbstractCrudController
{
    private Request $request;

    public function __construct(
        RequestStack $requestStack,
        private CourseService $courseService,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }
    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje CRUD w danym kontrolerze.
     * @return string Pełna nazwa klasy encji.
     */
    public static function getEntityFqcn(): string
    {
        return Course::class;
    }
    /**
     * W panelu administracyjnym funkcja konfuguruje odpowiedzi w zależności od aktualnej strony.
     * @param KeyValueStore $responseParameters Parametry odpowiedzi EasyAdmin.
     * @return KeyValueStore Zmodyfikowane lub domyślne parametry odpowiedzi.
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $pageName = $responseParameters->get('pageName');

        return match ($pageName) {
            Crud::PAGE_DETAIL => $this->courseService->pageDetail($responseParameters),
            default => $responseParameters,
        };
    }
    /**
     * W panelu admina konfuguruje widok CRUDA, ustawia tytuł strony szczegółów kursu oraz ustawia domyślny szablon Twig używany do renderowania widoku `Crud::PAGE_DETAIL`.
     * @param Crud $crud W EasyAdmin konfuguracja CRUD-a obiektu.
     * @return Crud Zaktualizowana konfiguracja CRUD-a.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('course.title')
            ->setEntityLabelInPlural('course.titleAll')
            ->setPageTitle(Crud::PAGE_INDEX, 'course.list')
            ->setPageTitle(Crud::PAGE_NEW, 'course.addCourse')
            ->setPageTitle(Crud::PAGE_EDIT, 'course.edit')
            ->setPageTitle(Crud::PAGE_DETAIL, 'course.details')
            ->overrideTemplate('crud/detail', 'course/schedule-page.html.twig');
    }
    /**
     * W panelu admina konfuguruje pole formularza i widoków Cruda dla kursu, metoda buduje i zwraca listę obiektów pól EasyAdmin, które mają być wyświetlane lub edytowane na różnych stronach (np. tworzenie, edycja, szczegóły).
     * @param string $pageName Nazwa strony.
     * @return iterable Zbiór skonfigurowanych pól do wyświetlenia na podanej stronie.
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->courseService->configureFields($pageName);
    }
    /**
     * W EasyAdmin konfiguruje i tworzy zapytanie dla listy. Dodanie filtorwania po kategorii kursu do `createIndexQueryBuilder`.
     * @param SearchDto $searchDto Obiekt DTO zawierający dane wyszukiwania.
     * @param EntityDto $entityDto Obiekt DTO z informacjami o encji.
     * @param FieldCollection $fields Kolekcja pól dostępnych w widoku.
     * @param FilterCollection $filters Kolekcja aktywnych filtrów EasyAdmin.
     * @return QueryBuilder Zapytanie QueryBuilder z ewentualnym filtrowaniem po kategorii.
     */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $category = $this->request->query->get('category');
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if (!empty($category)) {
            $qb->andWhere('entity.category = :category')
                ->setParameter('category', $category);
        }

        return $qb;
    }
    /**
     * Przypisanie uprawnień do akcji panelu admina.
     * @param Actions $actions Obiekt konfiguracji akcji EasyAdmin.
     * @return Actions Zaktualizowany obiekt konfiguracji z przypisanymi uprawnieniami.
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::INDEX, Permission::COURSE_VIEW->value);
        $actions->setPermission(Action::DETAIL, Permission::COURSE_VIEW->value);
        $actions->setPermission(Action::NEW, Permission::COURSE_CREATE->value);
        $actions->setPermission(Action::EDIT, Permission::COURSE_EDIT->value);
        $actions->setPermission(Action::DELETE, Permission::COURSE_DELETE->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }
}
