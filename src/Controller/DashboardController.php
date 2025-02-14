<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\Kurs;
use App\Entity\KursHarmonogram;
use App\Entity\Teoria;
use App\Entity\User;
use App\Enum\Kategoria;
use App\Entity\Pojazd;
use App\Enum\Role;
use App\Enum\Status;
use App\Repository\KursRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private KursRepository $kursRepository,
    ) {
        $this->kursRepository = $kursRepository;
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $argumenty = [];

        if ($this->isGranted((Role::ROLE_PRAKTYKANT)->value)) {
            $kursyPraktykant = $this->kursRepository->findBy(['praktykant' => $this->getUser()]);

            foreach ($kursyPraktykant as &$kurs) {
                $kurs->czas_trwania_praktyki = 0;
                foreach ($kurs->getHarmonogram() as $jazda) {
                    $kurs->czas_trwania_praktyki += $jazda->getCzasTrwania();
                }

                $kurs->czas_trwania_teorii = 0;
                foreach ($kurs->getTeoriaListaObecnoscis() as $teoria) {
                    $kurs->czas_trwania_teorii += $teoria->getTeoria()->getCzasTrwania();
                }
            }

            $argumenty['kursyPraktykant'] = $kursyPraktykant;
        }

        if ($this->isGranted((Role::ROLE_PRACOWNIK_PRAKTYKA)->value)) {
            $kursy = $this->kursRepository->findBy(['instruktor' => $this->getUser()]);
            $kursy = array_filter($kursy, fn($kurs) => $kurs->getStatus() != Status::Ukonczony);
            foreach ($kursy as &$kurs) {
                $kurs->czas_trwania_praktyki = 0;
                foreach ($kurs->getHarmonogram() as $jazda) {
                    $kurs->czas_trwania_praktyki += $jazda->getCzasTrwania();
                }

                $kurs->czas_trwania_teorii = 0;
                foreach ($kurs->getTeoriaListaObecnoscis() as $teoria) {
                    $kurs->czas_trwania_teorii += $teoria->getTeoria()->getCzasTrwania();
                }
            }
            $argumenty['kursy'] = $kursy;
        }

        return $this->render('dashboard.html.twig', $argumenty);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Osk');
    }

    public function configureMenuItems(): iterable
    {
        $kursy = [];
        // $kursy[] = MenuItem::linkToCrud('Ustawienia', 'fas fa-gear', Kurs::class);
        foreach (Kategoria::cases() as $kategoria) {
            $ilosc = $this->kursRepository->findBy(['kategoria' => $kategoria->value]);
            $ilosc = array_filter($ilosc, fn($kurs) => Status::Ukonczony != $kurs->getStatus());
            $ilosc = count($ilosc);

            $kursy[] = MenuItem::linkToCrud($kategoria->value . ($ilosc ? " - $ilosc" : ''), 'fas fa-square-parking', Kurs::class)->setQueryParameter('kategoria', $kategoria->value);
        }

        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Kalendarz', 'fas fa-calendar', 'calendar');

        yield MenuItem::section('Firma')
            ->setPermission(new Expression('!is_granted("' . (Role::ROLE_PRAKTYKANT)->value . '")'));

        yield MenuItem::subMenu('Kategorie', 'fas fa-square-parking')->setSubItems($kursy)
            ->setPermission(new Expression('is_granted("' . (Role::ROLE_PRACOWNIK_PRAKTYKA)->value . '")'));

        yield MenuItem::linkToCrud('Pracownicy', 'fas fa-users-rectangle', User::class)->setController(UserCrudController::class)
            ->setPermission(new Expression('is_granted("' . (Role::ROLE_ADMIN)->value . '")'));

        yield MenuItem::linkToCrud('Pojazdy', 'fas fa-car', Pojazd::class)
            ->setPermission(new Expression('is_granted("' . (Role::ROLE_ADMIN)->value . '")'));

        yield MenuItem::linkToCrud('Praktykanci', 'fas fa-users', User::class)->setController(PraktykanciCrudController::class)
            ->setPermission(new Expression('!is_granted("' . (Role::ROLE_PRAKTYKANT)->value . '")'));

        yield MenuItem::section('Akcje')
            ->setPermission(new Expression('!is_granted("' . (Role::ROLE_PRAKTYKANT)->value . '")'));

        yield MenuItem::linkToCrud('Dodaj Jazdę', 'fas fa-car', KursHarmonogram::class)->setAction('new')
            ->setPermission(new Expression('is_granted("' . (Role::ROLE_PRACOWNIK_PRAKTYKA)->value . '")'));

        yield MenuItem::linkToCrud('Dodaj Teorie', 'fas fa-book', Teoria::class)->setAction('new')
            ->setPermission(new Expression('is_granted("' . (Role::ROLE_PRACOWNIK_TEORIA)->value . '")'));

        yield MenuItem::section('');
        yield MenuItem::linkToCrud('Faq', 'fas fa-question', Faq::class);
    }
}
