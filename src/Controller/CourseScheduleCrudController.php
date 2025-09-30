<?php

namespace App\Controller;

use App\Enum\Permission;
use App\Entity\CourseSchedule;
use App\Service\CourseScheduleService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CourseScheduleCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private CourseScheduleService $courseScheduleService,
    ) {}

    /**
     * EasyAdmin wykorzystuje tą funkcje do określenia, na jakiej encji będą wykonywane operacje CRUD w danym kontrolerze.
     *  @return string FQCN encji obsługiwanej przez ten kontroler (`CourseSchedule::class`).
     */
    public static function getEntityFqcn(): string
    {
        return CourseSchedule::class;
    }

    /**
     * W panelu admina konfiguruje ustawienia wikoku crud-a dla harmonogramu kursu. Funkcja nadpisuje szblon Twiga do renderu widoku "course/schedule-page.html.twig"
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('schedule.title')
            ->setEntityLabelInPlural('schedule.list')
            ->setPageTitle(Crud::PAGE_INDEX, 'schedule.list')
            ->setPageTitle(Crud::PAGE_NEW, 'schedule.add')
            ->setPageTitle(Crud::PAGE_EDIT, 'schedule.edit')
            ->setPageTitle(Crud::PAGE_DETAIL, 'schedule.details')
            ->overrideTemplate('crud/detail', 'course/schedule-page.html.twig');
    }

    /**
     * Na podstawie "pageName" funkcja wybiera odpowiednia logike.
     * @param KeyValueStore $responseParameters
     * @return KeyValueStore
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $pageName = $responseParameters->get('pageName');

        return match ($pageName) {
            Crud::PAGE_DETAIL => $this->courseScheduleService->pageDetail($responseParameters),
            default => $responseParameters,
        };
    }

    /**
     * W panelu admina  funkcja konfiguruje pola formularza i widoków crud-a dla harmonogramu kursu. Buduje i zwraca listę obiektów pól EasyAdmin, które mają być wyświetlane lub edytowane na stronach tworzenia/edycji/szczegółów.
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return $this->courseScheduleService->configureFields($pageName);
    }

    /**
     * Funkcja obsługuje zapisywanie harmonogramu kursu w bazie danych.
     * @param EntityManagerInterface $entityManager
     * @param mixed                  $entityInstance
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->courseScheduleService->persistEntity($entityManager, $entityInstance);
    }

    /**
     * Dodatkowa funkcja cofnij dostępna w EasyAdmin, po wywołaniu przekierowuje na dashboard w sekcji kalenadarza.
     * @param AdminContext $context
     * @return Response
     */
    public function backToCalendar(
        AdminContext $context,
    ): Response {
        return $this->redirectToRoute('dashboard', ['routeName' => 'calendarIndex']);
    }

    /**
     * Konfiguracja encji kursów dla panelu EasyAdmin. Funkcja ustawia wymagania dla standardu crud-a i dodaje akcje i warunki wyświetlania dla odpowiednich ról użytkownia.
     * @param Actions $actions
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, Permission::COURSE_DELETE->value);
        $actions->setPermission(Action::EDIT, Permission::COURSE_EDIT->value);
        $actions->setPermission(Action::NEW, Permission::COURSE_CREATE->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        $actions->setPermission(
            Action::INDEX,
            new Expression("!is_granted('" . Permission::CUSTOMER->value . "')")
        );

        if ($this->isGranted(Permission::CUSTOMER->value)) {
            $actions
                ->add(Crud::PAGE_DETAIL, Action::new('backToCalendar', 'calendar.title')
                    ->linkToCrudAction('backToCalendar')
                    ->addCssClass('btn btn-primary'));
        }

        return $actions;
    }
}
