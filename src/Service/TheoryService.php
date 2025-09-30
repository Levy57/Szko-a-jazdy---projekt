<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use App\Entity\Theory;
use App\Enum\Status;
use App\Enum\Permission;
use App\Repository\UserRepository;
use App\Entity\TheoryAttendanceList;
use App\Repository\TheoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\TheoryAttendanceListRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TheoryService
{
    public function __construct(
        private readonly Security $security,
        private readonly UserService $userService,
        private readonly UserRepository $userRepository,
        private readonly TranslatorInterface $translator,
        private readonly TheoryRepository $theoryRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TheoryAttendanceListRepository $theoryAttendanceListRepository,
    ) {}

    public function pageDetail(KeyValueStore $responseParameters): KeyValueStore
    {
        $theory = $responseParameters->get('entity')->getInstance();

        $attendanceList = [];
        foreach ($theory->getAttendanceList() as $attendance) {
            $customer = $attendance->getCustomer();
            $attendanceList[] = [
                'id' => $customer->getUsername(),
                'text' => $customer->getUsername() . ' - ' . $customer->getFirstName() . ' ' . $customer->getLastName(),
            ];
        }

        $customers = [];
        foreach ($this->userService->getCustomers() as $customer) {
            $customers[] = ['id' => $customer->getUsername(), 'text' => $customer->getUsername() . ' - ' . $customer->getFirstName() . ' ' . $customer->getLastName()];
        }

        $responseParameters->set('customersJson', json_encode($customers));
        $responseParameters->set('attendanceList', $attendanceList);

        return $responseParameters;
    }


    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Theory) {
            return;
        }

        if (!$this->security->isGranted(Permission::ADMIN->value)) {
            $entityInstance->setEmployee($this->security->getUser());
        }
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function pageAttendanceList($data)
    {
        $theoryID = $data['theoryID'] ?? 0;
        $customers = $data['customers'] ?? [];

        $theory = $this->theoryRepository->findOneBy(['id' => $theoryID]);
        if (!$theory) {
            throw new Exception($this->translator->trans('error.entityNotFound'));
        }

        $theoryAttendanceList = $this->theoryAttendanceListRepository->findBy(['theory' => $theory]);
        $attendanceList = [];
        foreach ($theoryAttendanceList as $attendance) {
            $attendanceList[] = $attendance->getCustomer()->getUsername();
        }

        foreach ($customers as $customer) {
            if (in_array($customer, $attendanceList)) {
                continue;
            }
            $user = $this->userRepository->findOneBy(['username' => $customer]);
            if (!$user) {
                continue;
            }
            $obecnosc = new TheoryAttendanceList();
            $obecnosc->setTheory($theory);
            $obecnosc->setCustomer($user);
            $coursey = $user->getCourse();
            foreach ($coursey as $course) {
                if (Status::completed != $course->getStatus()) {
                    $obecnosc->addCourse($course);
                }
            }
            $this->entityManager->persist($obecnosc);
        }

        foreach ($theoryAttendanceList as $customer) {
            $customer = $customer->getCustomer();
            if (in_array($customer->getUsername(), $customers)) {
                continue;
            }
            $customer = $this->theoryAttendanceListRepository->findOneBy(['theory' => $theory, 'customer' => $customer]);
            $this->entityManager->remove($customer);
        }

        $this->entityManager->flush();
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            DateTimeField::new('startAt', 'startAt'),
            NumberField::new('duration', 'theory.duration'),
            TextField::new('title', 'theory.title'),
            TextareaField::new('description', 'description'),
        ];

        $employee = AssociationField::new('employee', 'employee')
            ->formatValue(fn($value) => $value->getUserName() . ' ' . $value->getFirstName() . ' ' . $value->getLastName()[0])
            ->setQueryBuilder(fn(QueryBuilder $qb) => $this->userService->getUserByRoleQueryBuilder($qb, Permission::EMPLOYEE_THEORY))
            ->setFormTypeOptions([
                'choice_label' => function ($course) {
                    return $course->getFirstName() . ' ' . $course->getLastName() . ' - ' . $course->getUserName();
                },
            ]);

        if (!$this->security->isGranted(Permission::ADMIN->value)) {
            $employee->hideWhenCreating()->setFormTypeOptions([
                'choice_label' => 'username',
                'data' => $this->security->getUser(),
            ]);
        }

        $fields[] = $employee;

        return $fields;
    }
}
