<?php

namespace App\Controller;

use App\Entity\Theory;
use App\Enum\Permission;
use App\Service\UserService;
use App\Service\TheoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TheoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Theory::class;
    }
    /**
    * Inicjalizuje kontroler/serwis z wymaganymi zależnościami.
    * @param EntityManagerInterface $entityManager Obsługuje operacje na bazie danych
    * @param UserService            $userService   Serwis do zarządzania użytkownikami
    * @param TheoryService          $theoryService Serwis do obsługi logiki związanej z teorią
    */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserService $userService,
        private TheoryService $theoryService,
    ) {}
    /**
     * Funkcja modyfikuje paramatyry generowane przez EasyAdmin w zależności od strony CRUD-a, Jeżeli użytkownik jest na stronie PAGE_DETAIL funkcja przesyłą logike do serwisu która pozwala na widok szeczegółów teorii. W pozostałych przypadkach zwracane są oryginalne parametry.
     * @param KeyValueStore $responseParameters EasyAdmin aktualne parametry odpowiedzi 
     * @return KeyValueStore Zmodyfikowane (lub oryginalne) parametry odpowiedzi
     */
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $pageName = $responseParameters->get('pageName');

        return match ($pageName) {
            Crud::PAGE_DETAIL => $this->theoryService->pageDetail($responseParameters),
            default => $responseParameters,
        };
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('theory')
            ->setEntityLabelInPlural('theory.lesson')
            ->setPageTitle(Crud::PAGE_INDEX, 'theory.list')
            ->setPageTitle(Crud::PAGE_NEW, 'theory.addTheory')
            ->setPageTitle(Crud::PAGE_EDIT, 'theory.edit')
            ->setPageTitle(Crud::PAGE_DETAIL, 'theory.details')
            ->overrideTemplate('crud/detail', 'attendance-list.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->theoryService->configureFields($pageName);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->theoryService->persistEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::INDEX, Permission::THEORY_VIEW->value);
        $actions->setPermission(Action::DETAIL, Permission::THEORY_VIEW->value);
        $actions->setPermission(Action::NEW, Permission::THEORY_CREATE->value);
        $actions->setPermission(Action::EDIT, Permission::THEORY_EDIT->value);
        $actions->setPermission(Action::DELETE, Permission::THEORY_DELETE->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        return $actions;
    }

    #[Route('/post-lista-obecnosci', name: 'attendance_list_post', methods: ['POST'])]
    public function attendanceListPost(Request $request)
    {
        $data = $request->request->all();
        $theoryID = $data['theoryID'];

        try {
            $this->theoryService->pageAttendanceList($data);
            $this->addFlash('success', 'theory.success');
        } catch (\Exception $error) {
            $this->addFlash('danger', 'theory.danger');
            return $this->redirectToRoute('dashboard');
        }

        return $this->redirectToRoute('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => TheoryCrudController::class, 'entityId' => $theoryID]);
    }
}
