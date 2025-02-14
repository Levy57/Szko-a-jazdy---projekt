<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Kategoria;
use App\Enum\Role;
use App\Enum\Status;
use Doctrine\ORM\EntityManagerInterface;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PraktykanciCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_DETAIL) {
            $entity = $responseParameters->get('entity');
            $praktykant = $entity->getInstance();

            $kursyPraktykant =  [];
            $jazdy = [];
            foreach ($praktykant->getKurs() as &$kurs) {
                $kursGodziny = 0;
                $kurs->czas_trwania_praktyki = 0;

                $harmonogram = $kurs->getHarmonogram()->toArray();
                usort($harmonogram, function ($a, $b) {
                    return $a->getStart() <=> $b->getStart();
                });

                foreach ($harmonogram as $jazda) {
                    $kursGodziny += $jazda->getCzasTrwania();
                    $jazda->czas_trwania_praktyki = $kursGodziny;
                    $jazda->razem_czas_trwania_praktyki = $kurs->getPraktykaGodziny();
                    $jazdy[] = $jazda;

                    $kurs->czas_trwania_praktyki += $jazda->getCzasTrwania();
                }

                $kurs->czas_trwania_teorii = 0;
                foreach ($kurs->getTeoriaListaObecnoscis() as $teoria) {
                    $kurs->czas_trwania_teorii += $teoria->getTeoria()->getCzasTrwania();
                }

                $kursyPraktykant[] = $kurs;
            }

            usort($jazdy, function ($a, $b) {
                return $b->getStart() <=> $a->getStart();
            });
            $responseParameters->set('jazdy', $jazdy);
            $responseParameters->set('kursyPraktykant', $kursyPraktykant);

            //////////////////////////////

            $teorieRepo = $praktykant->getTeoriaListaObecnoscis()->toArray();
            usort($teorieRepo, function ($a, $b) {
                return $b->getTeoria()->getStart() <=> $a->getTeoria()->getStart();
            });

            $responseParameters->set('teorie', $teorieRepo);
        }

        return $responseParameters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_DETAIL, 'Praktykant')
            ->overrideTemplate('crud/detail', 'praktykant.html.twig')
            ->setPageTitle(Crud::PAGE_NEW, 'Dodaj nowego praktykanta');
    }

    public function generujUsername(string $imie, string $nazwisko): string
    {
        $pierwsza = strtoupper(substr($imie, 0, 1));
        $druga = strtoupper(substr($nazwisko, 0, 1));

        $ilosc = $this->entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(User::class, 'u')
            ->where('u.username LIKE :prefix')
            ->setParameter('prefix', $pierwsza . $druga . '%')
            ->getQuery()
            ->getSingleScalarResult();

        return $pierwsza . $druga . str_pad($ilosc + 1, 2, '0', STR_PAD_LEFT);
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {

        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $qb->andWhere("entity.roles LIKE :role")
            ->setParameter('role', '%ROLE_PRAKTYKANT%');
        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {

        $kategoria_uprawnien = array_combine(
            array_map(fn($enum) => $enum->name, Kategoria::cases()),
            Kategoria::cases()
        );

        return [
            TextField::new('imie'),
            TextField::new('nazwisko'),
            TextField::new('numer_telefonu'),
            TextField::new('username', 'Identyfikator')->hideOnForm(),
            TextField::new('email'),
            TextField::new('password')->setFormType(PasswordType::class)->onlyOnForms(),
            ChoiceField::new('kategoria_uprawnien')
                ->setChoices($kategoria_uprawnien)
                ->allowMultipleChoices()
                ->renderAsBadges(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        if ($entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword);
        }

        $entityInstance->setUsername($this->generujUsername($entityInstance->getImie(), $entityInstance->getNazwisko()));

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            return;
        }

        if ($entityInstance->getPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword());
            $entityInstance->setPassword($hashedPassword);
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
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
