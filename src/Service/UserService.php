<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Enum\Permission;
use App\Enum\Role;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function getEmployees()
    {
        $qb = $this->userRepository->createQueryBuilder('entity');

        $this->getUserByRoleQueryBuilder(
            $qb,
            Permission::EMPLOYEE_COURSE,
            Permission::EMPLOYEE_THEORY
        );

        return $qb->getQuery()->getResult();
    }

    public function getCustomers()
    {
        $qb = $this->userRepository->createQueryBuilder('entity');

        $qb = $this->getUserByRoleQueryBuilder(
            $qb,
            Permission::CUSTOMER
        );

        return $qb->getQuery()->getResult();
    }


    public function getUserByRoleQueryBuilder($qb, Permission ...$permissions)
    {
        $orX = $qb->expr()->orX();

        foreach ($permissions as $index => $permission) {
            $paramName = 'permission_' . $index;
            $orX->add("JSON_CONTAINS(role.permissions, :$paramName, '$') = 1");
            $qb->setParameter($paramName, '"' . $permission->value . '"');
        }

        return $qb->join('entity.roles', 'role')
            ->andWhere($orX);
    }

    public function generateUsername(string $firstName, string $lastName, bool $employee = false): string
    {
        $firstNameLetter = strtoupper(substr($this->removeAccents($firstName), 0, 1));
        $lastNameLetter = strtoupper(substr($this->removeAccents($lastName), 0, 1));

        if ($employee) {
            $prefix = "P-$firstNameLetter$lastNameLetter";
        } else {
            $prefix = "$firstNameLetter$lastNameLetter";
        }

        // Znajdź najwyższy numer dla tego konkretnego prefiksu
        $existingUsernames = $this->entityManager->createQueryBuilder()
            ->select('u.username')
            ->from(User::class, 'u')
            ->where('u.username LIKE :prefix')
            ->setParameter('prefix', "$prefix%")
            ->getQuery()
            ->getScalarResult();

        $maxNumber = 0;
        foreach ($existingUsernames as $row) {
            $username = $row['username'];
            // Wyciągnij numer z końca username (np. "P-MK03" -> "03")
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $username, $matches)) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        return $prefix . str_pad((string) ($maxNumber + 1), 2, '0', STR_PAD_LEFT);
    }

    private function removeAccents(string $string): string
    {
        $accents = [
            'ą' => 'a',
            'ć' => 'c',
            'ę' => 'e',
            'ł' => 'l',
            'ń' => 'n',
            'ó' => 'o',
            'ś' => 's',
            'ź' => 'z',
            'ż' => 'z',
            'Ą' => 'A',
            'Ć' => 'C',
            'Ę' => 'E',
            'Ł' => 'L',
            'Ń' => 'N',
            'Ó' => 'O',
            'Ś' => 'S',
            'Ź' => 'Z',
            'Ż' => 'Z'
        ];

        return strtr($string, $accents);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance, $employee = false): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        if ($entityInstance->getPassword()) {
            $this->hashPassword($entityInstance);
        }

        $entityInstance->setUsername($this->generateUsername($entityInstance->getFirstName(), $entityInstance->getLastName(), $employee));

        if (!$employee) {
            $customerRole = $this->roleRepository->find(Role::CUSTOMER->value);

            if ($customerRole && !$entityInstance->getUserRoles()->contains($customerRole)) {
                $entityInstance->addRole($customerRole);
            }
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        if (empty($entityInstance->getPassword())) {
            $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            if (isset($originalUser['password'])) {
                $entityInstance->setPassword($originalUser['password']);
            }
        } else {
            $this->hashPassword($entityInstance);
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    private function hashPassword(User $entityInstance)
    {
        $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
        $entityInstance->setPassword($hashedPassword);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName', 'firstName'),
            TextField::new('lastName', 'lastName'),
            TextField::new('phoneNumber', 'phoneNumber'),
            TextField::new('username', 'username')->hideOnForm(),
            NumberField::new('courseEmployeeActive.count', 'userCrud.activeCourse')
                ->setCustomOption('sortable', false)
                ->hideOnForm(),
            TextField::new('email', 'email'),
            TextField::new('password', 'password')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Pozostaw puste aby nie zmieniać hasła'),
            AssociationField::new('categories', 'car.categories')
                ->autocomplete(), //TODO SHOW CATEGORIES NAME INSTEAD NUMBER OF RELATIONS
            // AssociationField::new('roles', 'roles')
            //     ->setQueryBuilder(function ($qb) {
            //         return $qb->where('entity.id != :excludeId')
            //             ->setParameter('excludeId', Role::CUSTOMER->value);
            //     }), TODO
        ];
    }

    /**
     * Na podstawie "pageName" funkcja wybiera odpowiednia logike.
     *
     * @param KeyValueStore $responseParameters
     * @return KeyValueStore
     */
    public function pageDetail(KeyValueStore $responseParameters): KeyValueStore
    {
        $employee = $responseParameters->get('entity')->getInstance();

        $courses = $employee->getCourseEmployeeActive();
        foreach ($courses as &$course) {
            $course->courseHoursSum = 0;
            foreach ($course->getSchedule() as $jazda) {
                $course->courseHoursSum += $jazda->getDuration();
            }

            $course->theoryHoursSum = 0;
            foreach ($course->getTheoryAttendanceLists() as $theory) {
                $course->theoryHoursSum += $theory->getTheory()->getDuration();
            }
        }
        $responseParameters->set('courses', $courses);
        return $responseParameters;
    }
}
