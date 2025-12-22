<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Referent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Referent>
 */
class ReferentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Referent::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des référent·es')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un référent·e')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le référent·e')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du référent·e')
            ->setEntityLabelInSingular('Référent·e')
            ->setEntityLabelInPlural('Référent·es')
            ->setDefaultSort(['nomFamille' => 'ASC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('prenom', 'Prénom');
        yield TextField::new('nomFamille', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TextField::new('telephone', 'Téléphone');
        yield TextField::new('fonction', 'Fonction');
        yield TextField::new('structure', 'Structure');
        yield AssociationField::new('utilisateur', 'Compte utilisateur associé')
            ->setFormTypeOption('choice_label', 'email')
            ->hideOnIndex();
    }
}
