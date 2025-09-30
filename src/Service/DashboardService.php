<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Status;
use App\Repository\UserRepository;
use App\Repository\CourseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DashboardService
{
    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly CourseRepository $courseRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }


    public function pageIndexCustomer(): array
    {
        $courses = $this->courseRepository->findBy(['customer' => $this->security->getUser()]);

        return [
            'courses' => $this->prepareCourses($courses)
        ];
    }

    public function pageIndex(): array
    {
        $courses = $this->courseRepository->findBy([
            'employee' => $this->security->getUser(),
            'status' => Status::completed->value
        ]);

        return [
            'courses' => $this->prepareCourses($courses)
        ];
    }

    private function prepareCourses($courses)
    {
        foreach ($courses as &$course) {
            $course->courseHours = 0;
            foreach ($course->getHarmonogram() as $jazda) {
                $course->courseHours += $jazda->getDuration();
            }

            $course->theoryHours = 0;
            foreach ($course->getTheoryAttendanceLists() as $theory) {
                $course->theoryHours += $theory->getTheory()->getDuration();
            }
        }
        return $courses;
    }
}
