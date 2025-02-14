<?php

namespace App\Controller;


use App\Entity\Pojazd;
use App\Enum\Kategoria;
use App\Enum\PojazdStan;
use App\Enum\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\ExpressionLanguage\Expression;

class PojazdCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Pojazd::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $StanyPojazdów = array_combine(
            array_map(fn($enum) => $enum->name, PojazdStan::cases()),
            PojazdStan::cases()
        );
        $Kategorie = array_combine(
            array_map(fn($enum) => $enum->name, Kategoria::cases()),
            Kategoria::cases()
        );

        return [
            TextField::new('marka'),
            IntegerField::new('rok')->hideOnIndex(),
            TextField::new('nazwa'),
            TextField::new('vin')->hideOnIndex(),
            ChoiceField::new('stan')
                ->setChoices($StanyPojazdów)
                ->autocomplete(),
            ChoiceField::new('kategoria')
                ->setChoices($Kategorie)
                ->allowMultipleChoices()
                ->renderAsBadges(),
            TextareaField::new('komentarz'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->setPermission(Action::DELETE, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::EDIT, (Role::ROLE_ADMIN)->value);
        $actions->setPermission(Action::NEW, (Role::ROLE_ADMIN)->value);

        $actions->add(Crud::PAGE_INDEX, Action::DETAIL);
        $actions->setPermission(Action::DETAIL, (Role::ROLE_ADMIN)->value);

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
        if (!$this->isGranted((Role::ROLE_ADMIN)->value)) {
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
