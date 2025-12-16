<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Session;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Session>
 */
class SessionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Session::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des sessions')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une session')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la session')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails de la session')
            ->setEntityLabelInSingular('Session')
            ->setEntityLabelInPlural('Sessions')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Nom de la session');
        yield DateField::new('dateDebut', 'Date début session');
        yield DateField::new('dateFin', 'Date fin session');
        yield ChoiceField::new('statut', 'Statut de la session')
            ->setChoices(Session::getStatutChoices());
        yield AssociationField::new('modelQuestionnaire', 'Questionnaire à associer')
            ->autocomplete()
            ->hideOnIndex();
    }
}
