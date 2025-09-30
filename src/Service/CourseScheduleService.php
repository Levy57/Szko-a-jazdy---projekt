<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Permission;
use App\Entity\CourseSchedule;
use App\Enum\Status;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CourseScheduleService
{
    public function __construct(
        private  Security $security,
        private  TranslatorInterface $translator,
        private  EntityManagerInterface $entityManager,
    ) {}


    public function pageDetail(KeyValueStore $responseParameters): KeyValueStore
    {
        $courseSchedule = $responseParameters->get('entity')->getInstance();
        $course = $courseSchedule->getCourse();

        $harmonogram = $course->getSchedule()->toArray();
        usort($harmonogram, function ($a, $b) {
            return $a->getStartAt() <=> $b->getStartAt();
        });

        $courseSchedules = [];
        $courseHours = 0;
        foreach ($harmonogram as $item) {
            $courseHours += $item->getDuration();
            $item->courseHours = $courseHours;
            $item->courseHoursSum = $course->getCourseHours();
            $courseSchedules[] = $item;
        }

        usort($courseSchedules, function ($a, $b) {
            return $b->getStartAt() <=> $a->getStartAt();
        });

        $responseParameters->set('coursesSchedule', $courseSchedules);

        return $responseParameters;
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('startAt', 'startAt');
        yield NumberField::new('duration', 'schedule.duration');
        yield AssociationField::new('course', 'schedule.course')
            ->formatValue(fn($value, $entity) => $value->getCustomer()->getUserName())
            ->setQueryBuilder(function (QueryBuilder $qb) {
                return $qb->Where('entity.status != :status')
                    ->setParameter('status', Status::completed);
            })
            ->setFormTypeOptions([
                'choice_label' => function ($course) {
                    return $course->getCustomer()->getUserName() . ' - ' . $course->getCustomer()->getfirstName() . ' ' . $course->getCustomer()->getLastName() . ' | ' . $this->translator->trans('course.category') .
                        ' ' . $course->getCategory()->getName();
                },
            ]);
        yield TextareaField::new('comment', 'schedule.comment');

        $employee = AssociationField::new('employee', 'employee')
            ->formatValue(fn($value, $entity) => $value->getUserName())
            ->setQueryBuilder(function (QueryBuilder $qb) {
                return $qb->join('entity.roles', 'role')
                    ->andWhere('JSON_CONTAINS(role.permissions, :permission1) = 1 OR JSON_CONTAINS(role.permissions, :permission2) = 1')
                    ->setParameter('permission1', '"' . Permission::EMPLOYEE_COURSE->value . '"')
                    ->setParameter('permission2', '"' . Permission::EMPLOYEE_THEORY->value . '"');
            })
            ->setFormTypeOptions([
                'choice_label' => function ($course) {
                    return $course->getfirstName() . ' ' . $course->getLastName() . ' - ' . $course->getUserName();
                },
            ]);

        if (!$this->security->isGranted(Permission::ADMIN->value)) {
            $employee->hideWhenCreating()->setFormTypeOptions([
                'choice_label' => 'username',
                'data' => $this->security->getUser(),
            ]);
        }

        yield $employee;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof CourseSchedule) {
            return;
        }

        if (!$this->security->isGranted(Permission::ADMIN->value)) {
            $entityInstance->setEmployee($this->security->getUser());
        }
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}
