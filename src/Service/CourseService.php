<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Status;
use App\Enum\Permission;
use Doctrine\ORM\QueryBuilder;
use App\Repository\CategoryRepository;
use App\Controller\CustomerCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class CourseService
{
    public function __construct(
        private UserService $userService,
        private TranslatorInterface $translator,
        private CategoryRepository $categoryRepository,
    ) {}

    public function pageDetail(KeyValueStore $responseParameters): KeyValueStore
    {
        $course = $responseParameters->get('entity')->getInstance();

        $coursesSchedules = [];
        $courseHours = 0;

        $schedule = $course->getSchedule()->toArray();

        usort($schedule, function ($a, $b) {
            return $a->getStartAt() <=> $b->getStartAt();
        });

        foreach ($schedule as $item) {
            $courseHours += $item->getDuration();
            $item->courseHours = $courseHours;
            $item->courseHoursSum = $course->getCourseHours();
            $coursesSchedules[] = $item;
        }

        usort($coursesSchedules, function ($a, $b) {
            return $b->getStartAt() <=> $a->getStartAt();
        });

        $responseParameters->set('coursesSchedule', $coursesSchedules);

        return $responseParameters;
    }

    /**
     * @param string $pageName
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('category', 'course.category');

        yield DateTimeField::new('startAt', 'course.startAt');

        yield DateTimeField::new('endedAt', 'course.endedAt')->hideWhenCreating();

        yield NumberField::new('theoryHours', 'theory.hours');

        yield NumberField::new('courseHours', 'course.hours');

        yield BooleanField::new('theory', 'theory')->setFormTypeOption('data', true);

        yield ChoiceField::new('status', 'course.status')->setChoices(Status::getArray($this->translator))->renderAsBadges();

        yield AssociationField::new('customer', 'customer')
            ->setFormTypeOptions(['choice_label' => 'username'])
            ->formatValue(fn($value) => $value->getUserName())
            ->setCrudController(CustomerCrudController::class)
            ->setQueryBuilder(fn(QueryBuilder $qb) => $this->userService->getUserByRoleQueryBuilder($qb, Permission::CUSTOMER));

        yield AssociationField::new('employee', 'course.employee')
            ->formatValue(fn($value) => $value->getUserName())
            ->setQueryBuilder(fn(QueryBuilder $qb) => $this->userService->getUserByRoleQueryBuilder($qb, Permission::EMPLOYEE_COURSE))
            ->setFormTypeOptions([
                'choice_label' => function ($user) {
                    $coursesActive = $user->getCourseEmployee();
                    $courseActiveSum = 0;
                    foreach ($coursesActive as $course) {
                        if ($course->getStatus() != Status::completed->value) {
                            ++$courseActiveSum;
                        }
                    }

                    return $user->getUserName() .
                        ' - ' . $courseActiveSum . ' - '. $this->translator->trans('course.category'). ': ' .
                        implode(
                            ', ',
                            array_map(
                                fn($category) => $category,
                                $user->getCategories()->toArray()
                            )
                        );
                },
            ]);
    }
}
