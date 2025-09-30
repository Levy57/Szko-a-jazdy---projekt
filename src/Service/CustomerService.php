<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CustomerService
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private TranslatorInterface $translator,
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}
    /**
     *
     * Generacja danych klienta takich jak kurs harmonogram i zajęcia teoretyczne
     * Funkcja przetwarza kursy i zlicza czas trwania praktyki i teorii, nastepnie sortuje harmonogramy według daty oraz ustawia dane w odpowiedziach.
     *
     * @param KeyValueStore $responseParameters
     *
     * @return KeyValueStore
     *
     */
    public function pageDetail(KeyValueStore $responseParameters): KeyValueStore
    {
        $customer = $responseParameters->get('entity')->getInstance();

        $courses = [];
        $coursesSchedule = [];
        foreach ($customer->getCourse() as $course) {
            $course->theoryHoursSum = 0;
            $course->courseHoursSum = 0;

            $schedule = $course->getSchedule()->toArray();
            usort($schedule, function ($a, $b) {
                return $a->getStartAt() <=> $b->getStartAt();
            });

            foreach ($schedule as $courseSchedule) {
                $course->courseHours += $courseSchedule->getDuration();

                $courseSchedule->courseHours = $course->courseHoursSum;
                $courseSchedule->courseHoursSum = $course->getPraktykaGodziny();
                $coursesSchedule[] = $courseSchedule;

                $course->courseHoursSum += $courseSchedule->getDuration();
            }

            foreach ($course->getTheoryAttendanceLists() as $theory) {
                $course->theoryHoursSum += $theory->getTheory()->getDuration();
            }

            $courses[] = $course;
        }

        usort($coursesSchedule, function ($a, $b) {
            return $b->getStartAt() <=> $a->getStartAt();
        });

        $responseParameters->set('coursesSchedule', $coursesSchedule);
        $responseParameters->set('courses', $courses);


        $theories = $customer->getTheoryAttendanceLists()->toArray();
        usort($theories, function ($a, $b) {
            return $b->getTheory()->getStartAt() <=> $a->getTheory()->getStartAt();
        });

        $responseParameters->set('theories', $theories);

        return $responseParameters;
    }


    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('username', 'username')->hideOnForm();
        yield TextField::new('email', 'email');
        yield TextField::new('firstName', 'firstName');
        yield TextField::new('lastName', 'lastName');
        yield TextField::new('phoneNumber', 'phoneNumber');
        yield TextField::new('password', 'password')->setFormType(PasswordType::class)->onlyOnForms();
        yield AssociationField::new('categories', 'car.categories')
            ->autocomplete();
    }
}
