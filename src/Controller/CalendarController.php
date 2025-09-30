<?php

namespace App\Controller;

use App\Service\CalendarService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CalendarController extends AbstractController
{
    public function __construct(
        private readonly CalendarService $calendarService
    ) {
    }

    /**
     * Wyświetla stronę kalendarza. Zalogowany użytkownik ma rolę administratora, pobierana jest lista pracowników z permisją CALENDARY_EMPLOYEES_VIEW
     *
     * @param Request $request Obiekt zapytania HTTP.
     * @return Response Odpowiedź HTTP z wyrenderowaną stroną.
     */
    #[Route([
        'pl' => '/kalendarz',
        'en' => '/calendary'
    ], name: 'calendarIndex')]
    public function index(Request $request): Response
    {
        $parameters = $this->calendarService->pageIndex();

        return $this->render('calendar/index.html.twig', $parameters);
    }

    /**
     * Zwraca w formacie JSON listę wydarzeń z kalendarza. Opcjonalnie przyjmie parametr GET `employeeUsername`, jak paramet nie jest podany zwaraca wszystkie wydarzenia.
     *
     * @param Request $request Obiekt zapytania HTTP, z którego pobierany `employeeUsername`.
     * @return JsonResponse Odpowiedź HTTP zawierająca dane wydarzeń kalendarza w formacie JSON.
     */
    #[Route('/calendary/events', name: 'calendarEvents')]
    public function events(Request $request): JsonResponse
    {
        $employeeUsername = $request->query->get('employeeUsername');
        $data = $this->calendarService->getCalendaryEventsJson($employeeUsername);

        return new JsonResponse($data);
    }
}
