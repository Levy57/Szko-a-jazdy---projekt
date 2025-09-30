<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Permission;
use App\Service\UserService;
use Doctrine\ORM\QueryBuilder;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use Symfony\Component\ExpressionLanguage\Expression;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function __construct(
        private UserService $userService,
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $pageName = $responseParameters->get('pageName');

        // $employee = $responseParameters->get('entity')->getInstance();
        // $responseParameters->set('employee', $employee);

        return match ($pageName) {
            Crud::PAGE_DETAIL => $this->userService->pageDetail($responseParameters),
            default => $responseParameters,
        };
    }

    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isGranted(Permission::CUSTOMER->value)) {
            $crud
                ->setEntityLabelInSingular('employee')
                ->setEntityLabelInPlural('employee.title')
                ->setPageTitle(Crud::PAGE_INDEX, 'employee.list')
                ->setPageTitle(Crud::PAGE_DETAIL, 'employee.details')
                ->setPageTitle(Crud::PAGE_NEW, 'employee.add')
                ->setPageTitle(Crud::PAGE_EDIT, 'employee.edit')
                ->overrideTemplate('crud/detail', 'shared/employee-contact.html.twig');
        } else {
            $crud
                ->setEntityLabelInSingular('employee')
                ->setEntityLabelInPlural('employee.title')
                ->setPageTitle(Crud::PAGE_INDEX, 'employee.list')
                ->setPageTitle(Crud::PAGE_DETAIL, 'employee.details')
                ->setPageTitle(Crud::PAGE_NEW, 'employee.add')
                ->setPageTitle(Crud::PAGE_EDIT, 'employee.edit')
                ->overrideTemplate('crud/detail', 'course/index.html.twig');
        }

        return $crud;
    }

    public function configureFields(string $pageName): iterable
    {

        return $this->userService->configureFields($pageName);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userService->persistEntity($entityManager, $entityInstance, true);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->userService->updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Crud::PAGE_INDEX, new Expression("is_granted('ADMIN') or is_granted('EMPLOYEE_COURSE') or is_granted('EMPLOYEE_THEORY')"));
        $actions->setPermission(Crud::PAGE_DETAIL, new Expression("is_granted('ADMIN') or is_granted('EMPLOYEE_COURSE') or is_granted('EMPLOYEE_THEORY')"));
        $actions->setPermission(Crud::PAGE_EDIT, Permission::ADMIN->value);
        $actions->setPermission(Crud::PAGE_NEW, Permission::ADMIN->value);

        $actions->setPermission(Action::DELETE, Permission::ADMIN->value);
        $actions->setPermission(Action::EDIT, Permission::ADMIN->value);
        $actions->setPermission(Action::NEW, Permission::ADMIN->value);
        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        if ($this->isGranted(Permission::CUSTOMER->value)) {
            $actions
                ->add(Crud::PAGE_DETAIL, Action::new('backToCalendar', 'calendar.title')
                    ->linkToCrudAction('backToCalendar')
                    ->addCssClass('btn btn-primary'));
        }

        return $actions;
    }

    public function backToCalendar(
        AdminContext $context,
    ): Response {
        return $this->redirectToRoute('dashboard', ['routeName' => 'calendarIndex']);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->join('entity.roles', 'role')
            ->andWhere('JSON_CONTAINS(role.permissions, :permission1) = 1 OR JSON_CONTAINS(role.permissions, :permission2) = 1')
            ->setParameter('permission1', '"' . Permission::EMPLOYEE_COURSE->value . '"')
            ->setParameter('permission2', '"' . Permission::EMPLOYEE_THEORY->value . '"');

        return $qb;
    }
}
