<?php

namespace App\Controller;

use App\Entity\KursHarmonogram;
use App\Entity\Teoria;
use App\Enum\KategoriaKolor;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends AbstractController
{


    public function __construct(
        private UserRepository $userRepository,
    ) {
        $this->userRepository = $userRepository;
    }


    #[Route('/kalendarz', name: 'calendar')]
    public function index(): Response
    {
        $argumenty = [];
        if ($this->isGranted((Role::ROLE_ADMIN)->value)) {
            $pracownicy = $this->userRepository->findAll();
            $pracownicy = array_filter($pracownicy, fn($pracownik) => in_array((Role::ROLE_PRACOWNIK_PRAKTYKA)->value, $pracownik->getRoles()));
            $argumenty['pracownicy'] = $pracownicy;
        }

        return $this->render('calendar.html.twig', $argumenty);
    }

    #[Route('/kalendarz/wydarzenia', name: 'calendar_events')]
    public function getEvents(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $events = $em->getRepository(KursHarmonogram::class)->findAll();

        if ($praktykant = (bool) $this->isGranted((Role::ROLE_PRAKTYKANT)->value))
            $events = array_filter($events, fn($event) => $event->getKurs()->getPraktykant()->getId() == $this->getUser()->getId());
        else 
        if (($this->isGranted((Role::ROLE_PRACOWNIK_PRAKTYKA)->value) || $this->isGranted((Role::ROLE_PRACOWNIK_TEORIA)->value)) && !$this->isGranted((Role::ROLE_ADMIN)->value))
            $events = array_filter($events, fn($event) => $event->getInstruktor()->getId() == $this->getUser()->getId());

        if ($this->isGranted((Role::ROLE_ADMIN)->value)) {
            $pracownikID = $request->query->get('pracownikID') ?? false;
            if ($pracownikID && $pracownikID != 'all') {
                $events = array_filter($events, fn($event) => $event->getInstruktor()->getUsername() == $pracownikID);
            }
        }

        $data = [];
        foreach ($events as $event)
            $data[] = [
                'typ' => 'praktyka',
                'title' =>
                $praktykant ?
                    "kat. {$event->getKurs()->getKategoria()->value} - {$event->getInstruktor()->getImie()} {$event->getInstruktor()->getNazwisko()[0]}"
                    :
                    "{$event->getKurs()->getKategoria()->value} - {$event->getKurs()->getPraktykant()->getImie()} {$event->getKurs()->getPraktykant()->getNazwisko()[0]}",
                'url' => $this->generateUrl('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => 'App\\Controller\\KursHarmonogramCrudController', 'entityId' => $event->getId()]),
                'url_edit' => $this->generateUrl('dashboard', ['crudAction' => 'edit', 'crudControllerFqcn' => 'App\\Controller\\KursHarmonogramCrudController', 'entityId' => $event->getId()]),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getStart()->add(new \DateInterval("PT{$event->getCzasTrwania()}H"))->format('Y-m-d H:i:s'),
                'backgroundColor' => (new ReflectionEnum(KategoriaKolor::class))->getCase($event->getKurs()->getKategoria()->value)->getValue(),
                'borderColor' => (new ReflectionEnum(KategoriaKolor::class))->getCase($event->getKurs()->getKategoria()->value)->getValue(),
            ];

        $events = $em->getRepository(Teoria::class)->findAll();
        foreach ($events as $event)
            $data[] = [
                'typ' => 'teoria',
                'title' => "Zajęcia teoretyczne",
                'url' => $this->generateUrl('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => 'App\\Controller\\TeoriaCrudController', 'entityId' => $event->getId()]),
                'url_edit' => $this->generateUrl('dashboard', ['crudAction' => 'edit', 'crudControllerFqcn' => 'App\\Controller\\TeoriaCrudController', 'entityId' => $event->getId()]),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getStart()->add(new \DateInterval("PT{$event->getCzasTrwania()}H"))->format('Y-m-d H:i:s'),
                'backgroundColor' => '#334a35',
                'borderColor' => '#334a35',
            ];

        return new JsonResponse(array_values($data));
    }
}
