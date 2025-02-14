<?php

namespace App\Controller;

use App\Entity\Kurs;
use App\Enum\Kategoria;
use App\Enum\Role;
use App\Enum\Status;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class KursCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Kurs::class;
    }

    private Request $request;
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_DETAIL) {
            $entity = $responseParameters->get('entity');

            $kurs = $entity->getInstance();
            $jazdy = [];
            $kursGodziny = 0;

            $harmonogram = $kurs->getHarmonogram()->toArray();

            usort($harmonogram, function ($a, $b) {
                return $a->getStart() <=> $b->getStart();
            });

            foreach ($harmonogram as $jazda) {
                $kursGodziny += $jazda->getCzasTrwania();
                $jazda->czas_trwania_praktyki = $kursGodziny;
                $jazda->razem_czas_trwania_praktyki = $kurs->getPraktykaGodziny();
                $jazdy[] = $jazda;
            }

            usort($jazdy, function ($a, $b) {
                return $b->getStart() <=> $a->getStart();
            });

            $responseParameters->set('jazdy', $jazdy);
            $responseParameters->set('praktykant', (bool) $this->isGranted((Role::ROLE_PRAKTYKANT)->value));
        }

        return $responseParameters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_DETAIL, 'Kurs')
            ->overrideTemplate('crud/detail', 'ostatnie-jazdy.html.twig');
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $kategoria = $this->request->query->get("kategoria");
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        if (!empty($kategoria)) {
            $qb->andWhere("entity.kategoria = :kategoria")
                ->setParameter('kategoria', $kategoria);
        }
        return $qb;
    }
    public function configureFields(string $pageName): iterable
    {

        $Kategorie = array_combine(
            array_map(fn($enum) => $enum->name, Kategoria::cases()),
            Kategoria::cases()
        );
        $Statusy = array_combine(
            array_map(fn($enum) => $enum->name, Status::cases()),
            Status::cases()
        );
        return [
            ChoiceField::new('kategoria')
                ->setChoices($Kategorie)
                ->renderAsBadges(),
            DateTimeField::new('start_kurs', 'Rozpoczęcie kursu'),
            DateTimeField::new('end_kurs', 'Zakończenie kursu')->hideWhenCreating(),
            NumberField::new('teoria_godziny'),
            NumberField::new('praktyka_godziny'),
            BooleanField::new('teoria')->setFormTypeOption('data', true),
            ChoiceField::new('status')
                ->setChoices($Statusy)
                ->renderAsBadges(),
            AssociationField::new('praktykant')
                ->setFormTypeOptions(['choice_label' => 'username'])
                ->formatValue(fn($value, $entity) => $value->getUserName())
                ->setCrudController(PraktykanciCrudController::class)
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->Where("entity.roles LIKE :role")
                        ->setParameter('role', '%ROLE_PRAKTYKANT%');
                }),
            AssociationField::new('instruktor', 'Opiekun kursu')
                ->formatValue(fn($value, $entity) => $value->getUserName())
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->Where("entity.roles LIKE :role")
                        ->setParameter('role', '%ROLE_PRACOWNIK_PRAKTYKA%');
                })->setFormTypeOptions([
                    'choice_label' => function ($user) {
                        $kursy = $user->getKursInstruktor();
                        $iloscAktywnychKursow = 0;
                        foreach ($kursy as $kurs)
                            if ($kurs->getStatus() != (Status::Ukonczony)->value)
                                $iloscAktywnychKursow++;
                        return $user->getUserName() . ' - ' . $iloscAktywnychKursow . ' - kat. ' . implode(', ', array_map(fn($kategoria) => $kategoria->value, $user->getKategoriaUprawnien()));
                    }
                ])

        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::EDIT, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::NEW, (Role::ROLE_ADMIN)->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        $actions->setPermission(
            Action::INDEX,
            new Expression("!is_granted('" . Role::ROLE_PRAKTYKANT->value . "')")
        );

        return $actions;
    }

    public function index(AdminContext $context)
    {
        if ($this->isGranted((Role::ROLE_PRAKTYKANT)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::index($context);
    }
    public function detail(AdminContext $context)
    {
        if ($this->isGranted((Role::ROLE_PRAKTYKANT)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::detail($context);
    }
    public function new(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::new($context);
    }
    public function edit(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::edit($context);
    }
    public function delete(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::delete($context);
    }
}
