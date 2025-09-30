<?php

namespace App\Controller;

use App\Entity\Car;
use App\Entity\CarCondition;
use App\Entity\Category;
use App\Entity\Course;
use App\Entity\CourseSchedule;
use App\Entity\Faq;
use App\Entity\Theory;
use App\Entity\User;
use App\Enum\Permission;
use App\Enum\Status;
use App\Repository\CategoryRepository;
use App\Repository\CourseRepository;
use App\Service\DashboardService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController //TODO consider service?
{
    public function __construct(
        private CourseRepository $courseRepository,
        private CategoryRepository $categoryRepository,
        private DashboardService $dashboardService
    ) {}

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('css/admin.css');
    }

    #[Route('/', name: 'dashboard')]
    /**
     * W panelu admina wyświtla dashboard. 
     * @return Response Odpowiedź HTTP z wyrenderowanym szablonem dashboardu.
     */
    public function index(): Response
    {
        if ($this->isGranted(Permission::CUSTOMER->value)) {
            $parameters = $this->dashboardService->pageIndexCustomer();
            return $this->render('dashboard-customer.html.twig', $parameters);
        }

        $parameters = $this->dashboardService->pageIndex();

        return $this->render('dashboard.html.twig', $parameters);
    }
    /**
     * Funkcja tworzy nowy 'Dashboard` i ustawia jego tytuł na „Osk”.
     * @return Dashboard Skonfigurowany obiekt Dashboard dla EasyAdmin.
     */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Osk');
    }
    /**
     * Funkcja konfiguruje menu w palenu EasyAdmin i nadpisuje domyślne menu użytkownika, dodaje możliwość wyboru jezyka interfejsu.
     * @param UserInterface $user Obiekt aktualnie zalogowanego użytkownika.
     * @return UserMenu Zaktualizowane menu użytkownika z nowymi elementami.
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::section('language'),
                MenuItem::linkToRoute('🇵🇱 Polski', null, 'localeChange', ['locale' => 'pl']),
                MenuItem::linkToRoute('🇬🇧 English', null, 'localeChange', ['locale' => 'en']),
            ]);
    }

    public function configureMenuItems(): iterable
    {
        $categories = [];
        foreach ($this->categoryRepository->findAll() as $category) {
            $categorySum = count($this->courseRepository->findBy([
                'category' => $category,
                'status' => Status::completed->value
            ]) ?? []);
            $categories[] = MenuItem::linkToCrud("{$category->getName()} - $categorySum", 'fas fa-square-parking', Course::class)
                ->setQueryParameter('category', $category->getId());
        }

        yield MenuItem::linkToDashboard('dashboard', 'fa fa-home');

        yield MenuItem::section('actions');

        yield MenuItem::linkToCrud('course.addCourse', 'fas fa-car', Course::class)->setAction('new')
            ->setPermission(Permission::COURSE_CREATE->value);

        yield MenuItem::linkToCrud('schedule.add', 'fas fa-car', CourseSchedule::class)->setAction('new')
            ->setPermission(Permission::COURSE_CREATE->value);

        yield MenuItem::linkToCrud('theory.addTheory', 'fas fa-book', Theory::class)->setAction('new')
            ->setPermission(Permission::THEORY_CREATE->value);

        yield MenuItem::section('');

        yield MenuItem::linkToRoute('calendar.title', 'fas fa-calendar', 'calendarIndex');

        yield MenuItem::linkToCrud('theory.list', 'fas fa-book-open', Theory::class)
            ->setPermission(Permission::THEORY_VIEW->value);

        yield MenuItem::linkToCrud('schedule.list', 'fas fa-calendar-days', CourseSchedule::class)
            ->setPermission(Permission::COURSE_VIEW->value);

        yield MenuItem::subMenu('categories', 'fas fa-square-parking')->setSubItems($categories)
            ->setPermission(new Expression('is_granted("' . Permission::COURSE_CREATE->value . '") || is_granted("' . Permission::THEORY_CREATE->value . '")'));

        yield MenuItem::section('');

        yield MenuItem::linkToCrud('employee.title', 'fas fa-users-rectangle', User::class)->setController(UserCrudController::class)
            ->setPermission(Permission::ADMIN->value);

        yield MenuItem::linkToCrud('car.title', 'fas fa-car', Car::class)
            ->setPermission(Permission::CAR_VIEW->value);

        yield MenuItem::linkToCrud('customer.title', 'fas fa-users', User::class)->setController(CustomerCrudController::class)
            ->setPermission(Permission::CUSTOMER_VIEW->value);

        yield MenuItem::section('');

        yield MenuItem::linkToCrud('Faq', 'fas fa-question', Faq::class);

        yield MenuItem::section('');

        yield MenuItem::subMenu('settings', 'fas fa-gear')->setSubItems([
            MenuItem::linkToCrud('category.title', 'fas fa-book', Category::class),
            MenuItem::linkToCrud('carCondition.title', 'fas fa-book', CarCondition::class)
        ])->setPermission(Permission::ADMIN->value);
    }
}
