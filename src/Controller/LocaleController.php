<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LocaleController extends AbstractController
{
    /**
     * Zmiana wyświetalnego języka zalogowanego użytkownika
     * @param string $locale
     * @return RedirectResponse
     */
    #[Route('/locale/{locale}/change', name: 'localeChange')]
    public function changeLocale(string $locale, SecurityController $security, EntityManagerInterface $em, RequestStack $requestStack): RedirectResponse
    {
        /** @var User $user */
        $user = $security->getUser();

        if ($user) {
            $user->setLocale($locale);
            $em->flush();
        }
        $requestStack->getSession()->set('_locale', $locale);

        return $this->redirect($requestStack->getCurrentRequest()->headers->get('referer', '/'));
    }
}
