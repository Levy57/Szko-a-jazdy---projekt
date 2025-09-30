<?php

declare(strict_types=1);

namespace App\Service;

use App\Controller\CourseScheduleCrudController;
use App\Entity\Theory;
use App\Enum\Permission;
use App\Entity\CourseSchedule;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarService
{
    public function __construct(
        private readonly Security $security,
        private readonly UserService $userService,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {}
    /**
     * Funkcja przygotowuje kalendarz do wyświetlenia na głownej stronie i sprawdza czy zalogowany ma uprawnienia, jeżeli tak to pobiera liste pracowników z UserService, w przeciwnym wypadku zwraca pustą listre
     *
     * @return array

     */
    public function pageIndex(): array
    {
        $employees = [];
        if ($this->security->isGranted(Permission::CALENDARY_EMPLOYEES_VIEW->value)) {
            $employees = $this->userService->getEmployees();
        }

        return [
            'employees' => $employees
        ];
    }

    /**
     * Pobieranie listy wydarzeń kalendarza w formacie tablicy,
     *
     * Dane są zależne od roli zalogowanego:
     * Jak ADMIN to pokazuje wszystkich pracowników,
     * Jak CUSTOMER pobiera inforamcje dla danego pracownika,
     * jeśli pracownik teorii lub instruktor jazdy to pobieda dane przypisane do jego konta.
     *
     * @param string|null $employeeUsername
     *
     * @return array
     */
    public function getCalendaryEventsJson($employeeUsername = null): array
    {
        /** @var User $customer */
        $customer = $this->security->getUser();

        $isCustomer = $this->security->isGranted(Permission::CUSTOMER->value);
        if ($isCustomer) {
            $events = $this->getCustomerEvents();
        }

        $isAdmin = $this->security->isGranted(Permission::ADMIN->value);
        if ($isAdmin) {
            $events = $this->getEmployeeEvents($employeeUsername);
        }

        $isEmployee = (bool) !$this->security->isGranted(Permission::CUSTOMER->value);
        if ($isEmployee && !$isAdmin) {
            $events = $this->getEmployeeEvents($customer->getUsername());
        }

        $data = [];
        foreach ($events as $event) {
            $data[] = [
                'type' => 'course',
                'title' => "{$event->getCourse()->getCategory()->getName()} - {$event->getCourse()->getCustomer()->getFirstName()} {$event->getCourse()->getCustomer()->getLastName()[0]}",
                'url' => $this->urlGenerator->generate('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => CourseScheduleCrudController::class, 'entityId' => $event->getId()]),
                'url_edit' => $this->urlGenerator->generate('dashboard', ['crudAction' => 'edit', 'crudControllerFqcn' => 'App\\Controller\\CourseScheduleCrudController', 'entityId' => $event->getId()]),
                'start' => $event->getStartAt()
                    ->format('Y-m-d H:i:s'),
                'end' => $event->getStartAt()
                    ->add(new \DateInterval("PT{$event->getDuration()}H"))
                    ->format('Y-m-d H:i:s'),
                'backgroundColor' => $event->getCourse()->getCategory()->getColor(),
                'borderColor' => $event->getCourse()->getCategory()->getColor(),
            ];
        }

        $events = $this->entityManager->getRepository(Theory::class)->findAll();
        foreach ($events as $event) {
            $data[] = [
                'type' => 'theory',
                'title' => $this->translator->trans('theory.lesson'),
                'url' => $this->urlGenerator->generate('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => 'App\\Controller\\TheoryCrudController', 'entityId' => $event->getId()]),
                'url_edit' => $this->urlGenerator->generate('dashboard', ['crudAction' => 'edit', 'crudControllerFqcn' => 'App\\Controller\\TheoryCrudController', 'entityId' => $event->getId()]),
                'start' => $event->getStartAt()
                    ->format('Y-m-d H:i:s'),
                'end' => $event->getStartAt()
                    ->add(new \DateInterval("PT{$event->getDuration()}H"))
                    ->format('Y-m-d H:i:s'),
                'backgroundColor' => '#334a35',
                'borderColor' => '#334a35',
            ];
        }

        return array_values($data);
    }
    /**
     * Pobieranie wydarzeń przypisanych do zalogowanego użytkownika
     *
     * Pobiera wszystkie harmonogramy kursów następnie filtruje po indentyfikatorze który jest zalogowany.
     *
     * @return array
     */
    private function getCustomerEvents()
    {
        $events = $this->entityManager->getRepository(CourseSchedule::class)->findAll();
        /** @var User $customer */
        $customer = $this->security->getUser();

        return array_filter(
            $events,
            fn($event) =>
            $event->getCourse()->getCustomer()->getId() == $customer->getId()
        );
    }
    /**
     * Pobieranie wydarzeń z kalendarza przypisanych do danego pracownika
     *
     * Pobiera wszystkie harmonogramy kursów następnie przypisane filtruje po indentyfikatorze dla danego pracownika.
     *
     * @param string|null $employeeUsername
     * @return array
     */
    private function getEmployeeEvents($employeeUsername = null)
    {
        $events = $this->entityManager->getRepository(CourseSchedule::class)->findAll();

        if (!$employeeUsername || $employeeUsername == 'all')
            return $events;

        return array_filter(
            $events,
            fn($event) =>
            $event->getEmployee()->getUsername() == $employeeUsername
        );
    }
}
