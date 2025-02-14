<?php

namespace App\Controller;

use App\Entity\Teoria;
use App\Entity\TeoriaListaObecnosci;
use App\Entity\User;
use App\Enum\Role;
use App\Enum\Status;
use App\Repository\TeoriaListaObecnosciRepository;
use App\Repository\TeoriaRepository;
use App\Repository\UserRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeoriaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Teoria::class;
    }

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TeoriaListaObecnosciRepository $teoriaListaObecnosciRepository,
        private TeoriaRepository $teoriaRepository,
        private UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->teoriaRepository = $teoriaRepository;
        $this->teoriaListaObecnosciRepository = $teoriaListaObecnosciRepository;
        $this->userRepository = $userRepository;
    }

    #[Route('/post-lista-obecnosci', name: 'lista_obecnosci_post', methods: ['POST'])]
    public function listaObecnosciPost(Request $request)
    {
        $data = $request->request->all();
        $teoriaID = $data['teoriaID'];
        $teoria = $this->teoriaRepository->findOneBy(['id' => $teoriaID]);
        if (!$teoria)
            return $this->redirectToRoute('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => TeoriaCrudController::class, 'entityId' => $id]);
        $praktykanci = $data['praktykanci'] ?? [];

        $lista = $this->teoriaListaObecnosciRepository->findBy(['teoria' => $teoria]);
        $listaObecnosci = [];
        foreach ($lista as $l)
            $listaObecnosci[] = $l->getPraktykant()->getUsername();

        foreach ($praktykanci as $praktykant) {
            if (in_array($praktykant, $listaObecnosci)) continue;
            $user = $this->userRepository->findOneBy(['username' => $praktykant]);
            if (!$user) continue;
            $obecnosc = new TeoriaListaObecnosci();
            $obecnosc->setTeoria($teoria);
            $obecnosc->setPraktykant($user);
            $kursy = $user->getKurs();
            foreach ($kursy as $kurs)
                if ($kurs->getStatus() != Status::Ukonczony)
                    $obecnosc->addKur($kurs);
            $this->entityManager->persist($obecnosc);
        }

        foreach ($lista as $praktykant) {
            $praktykant = $praktykant->getPraktykant();
            if (in_array($praktykant->getUsername(), $praktykanci)) continue;
            $praktykant = $this->teoriaListaObecnosciRepository->findOneBy(['teoria' => $teoria, 'praktykant' => $praktykant]);
            $this->entityManager->remove($praktykant);
        }

        try {
            $this->entityManager->flush();
        } catch (\Exception $error) {
            $this->addFlash('danger', 'Wystąpił błąd podczas aktualizowania listy obecności!');
        }

        $this->addFlash('success', 'Lista obecności pomyślnie zaktualizowana!');
        return $this->redirectToRoute('dashboard', ['crudAction' => 'detail', 'crudControllerFqcn' => TeoriaCrudController::class, 'entityId' => $teoriaID]);
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        if ($responseParameters->get('pageName') === Crud::PAGE_DETAIL) {
            $entity = $responseParameters->get('entity');
            $teoria = $entity->getInstance();

            if ($this->isGranted((Role::ROLE_PRAKTYKANT)->value)) {
                $obecny = false;
                foreach ($teoria->getListaObecnosci() as $t)
                    if ($t->getPraktykant()->getUsername() == $this->getUser()->getUsername())
                        $obecny = true;
                $responseParameters->set('praktykantObecnosc', $obecny);
            } else {
                $praktykanci = $this->entityManager->createQueryBuilder()
                    ->select('u')
                    ->from(User::class, 'u')
                    ->where('u.roles LIKE :roles')
                    ->setParameter('roles', '%ROLE_PRAKTYKANT%')
                    ->getQuery()
                    ->getResult();

                $listaObecnosci = [];
                foreach ($teoria->getListaObecnosci() as &$praktykant) {
                    $praktykant = $praktykant->getPraktykant();
                    $listaObecnosci[] = [
                        'id' => $praktykant->getUsername(),
                        'text' => $praktykant->getUsername() . ' - ' . $praktykant->getImie() . ' ' . $praktykant->getNazwisko()
                    ];
                }

                foreach ($praktykanci as &$praktykant)
                    $praktykant = ['id' => $praktykant->getUsername(), 'text' => $praktykant->getUsername() . ' - ' . $praktykant->getImie() . ' ' . $praktykant->getNazwisko()];
                $praktykanci = json_encode($praktykanci);

                $responseParameters->set('praktykanciJson', $praktykanci);
                $responseParameters->set('listaObecnosci', $listaObecnosci);
            }
        }

        return $responseParameters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_DETAIL, 'Zajęcia teoretyczne')
            ->overrideTemplate('crud/detail', 'lista-obecnosci.html.twig');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            DateTimeField::new('start'),
            NumberField::new('czas_trwania', "Czas trwania (h)"),
            TextField::new('temat'),
            TextareaField::new('opis'),
        ];

        $instruktor = AssociationField::new('instruktor')
            ->formatValue(fn($value) => $value->getUserName() . ' ' . $value->getImie() . ' ' . $value->getNazwisko()[0])
            ->setQueryBuilder(function (QueryBuilder $qb) {
                return $qb->Where("entity.roles LIKE :role")
                    ->setParameter('role', '%ROLE_PRACOWNIK_TEORIA%');
            })
            ->setFormTypeOptions([
                'choice_label' => function ($kurs) {
                    return $kurs->getImie() . ' ' . $kurs->getNazwisko() . ' - ' . $kurs->getUserName();
                },
            ]);

        if (!$this->isGranted((Role::ROLE_ADMIN)->value))
            $instruktor->hideWhenCreating()->setFormTypeOptions([
                'choice_label' => 'username',
                'data' => $this->getUser()
            ]);

        $fields[] = $instruktor;

        return $fields;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Teoria) {
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
        $actions->setPermission(Action::DELETE, (Role::ROLE_PRACOWNIK_TEORIA)->value);
        $actions->setPermission(Action::EDIT, (Role::ROLE_PRACOWNIK_TEORIA)->value);
        $actions->setPermission(Action::NEW, (Role::ROLE_PRACOWNIK_TEORIA)->value);

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
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_TEORIA)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::new($context);
    }
    public function edit(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_TEORIA)->value)) {
            throw new ForbiddenActionException($context);
        }
        return parent::edit($context);
    }
    public function delete(AdminContext $context)
    {
        if (!$this->isGranted((Role::ROLE_PRACOWNIK_TEORIA)->value)) {
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
