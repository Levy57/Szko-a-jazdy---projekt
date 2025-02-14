<?php

namespace App\Controller;

use App\Entity\KursHarmonogram;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;

class KursHarmonogramCrudController extends AbstractCrudController
{

    public function __construct(
        private Security $security
    ) {
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return KursHarmonogram::class;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_DETAIL) {
            $entity = $responseParameters->get('entity');

            $obecnaJazda = $entity->getInstance();
            $kurs = $obecnaJazda->getKurs();
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
            ->setPageTitle(Crud::PAGE_DETAIL, 'Jazda praktyczna')
            ->overrideTemplate('crud/detail', 'ostatnie-jazdy.html.twig');
    }


    public function configureFields(string $pageName): iterable
    {
        $fields = [
            DateTimeField::new("start"),
            NumberField::new("czas_trwania", "Czas trwania (h)"),
            AssociationField::new('kurs', "Praktykant")
                ->formatValue(fn($value, $entity) => $value->getPraktykant()->getUserName())
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb->Where("entity.status != :status")
                        ->setParameter('status', 'Ukończony');
                })
                ->setFormTypeOptions([
                    'choice_label' => function ($kurs) {
                        return $kurs->getPraktykant()->getImie() . ' ' . $kurs->getPraktykant()->getNazwisko() . ' - ' . $kurs->getPraktykant()->getUserName() . ' - kat. ' . $kurs->getKategoria()->value;
                    }
                ]),
            TextareaField::new("komentarz"),
        ];

        $instruktor = AssociationField::new('instruktor')
            ->formatValue(fn($value, $entity) => $value->getUserName())
            ->setQueryBuilder(function (QueryBuilder $qb) {
                return $qb->Where("entity.roles NOT LIKE :role")
                    ->setParameter('role', '%ROLE_PRAKTYKANT%');
            })
            ->setFormTypeOptions([
                'choice_label' => function ($kurs) {
                    return $kurs->getImie() . ' ' . $kurs->getNazwisko() . ' - ' . $kurs->getUserName();
                },
            ]);

        if (!$this->security->isGranted((Role::ROLE_ADMIN)->value))
            $instruktor->hideWhenCreating()->setFormTypeOptions([
                'choice_label' => 'username',
                'data' => $this->security->getUser()
            ]);

        $fields[] = $instruktor;

        return $fields;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof KursHarmonogram) {
            return;
        }

        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
            $entityInstance->setInstruktor($this->getUser());
            $entityManager->persist($entityInstance);
            $entityManager->flush();
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, (Role::ROLE_PRACOWNIK_PRAKTYKA)->value);
        $actions->setPermission(Action::EDIT, (Role::ROLE_PRACOWNIK_PRAKTYKA)->value);
        $actions->setPermission(Action::NEW, (Role::ROLE_PRACOWNIK_PRAKTYKA)->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);

        $actions->setPermission(
            Action::INDEX,
            new Expression("!is_granted('" . Role::ROLE_PRAKTYKANT->value . "')")
        );

        if ($this->isGranted((Role::ROLE_PRAKTYKANT)->value))
            $actions
                ->add(Crud::PAGE_DETAIL, Action::new('cofnijAction', 'Kalendarz')
                    ->linkToCrudAction('cofnijAction')
                    ->addCssClass('btn btn-primary'));

        return $actions;
    }

    public function index(AdminContext $context)
    {
        return $this->redirectToRoute('dashboard', ['routeName' => 'calendar']);
    }
    public function new(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_PRAKTYKA)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::new($context);
    }
    public function edit(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_PRAKTYKA)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::edit($context);
    }
    public function delete(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_PRAKTYKA)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::delete($context);
    }

    public function cofnijAction(
        AdminContext $context
    ): Response {
        return $this->redirectToRoute('dashboard', ['routeName' => 'calendar']);
    }
}
