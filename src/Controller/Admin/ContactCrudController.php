<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\ValueObject\Type;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Contact>
 */
class ContactCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Contact')
            ->setEntityLabelInPlural('Contacts')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des contacts')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Contact $contact) => (string) $contact)
            ->setDefaultSort(['id' => 'DESC'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var ?Contact $contact */
        $contact = $this->getContext()?->getEntity()->getInstance();
        $renderProFields = Type::EMPLOYEUR === $contact?->getType();

        yield FormField::addPanel('Informations de contact');
        yield ChoiceField::new('type', 'Type')
            ->setChoices(Type::choiceOptions())
            ->renderAsBadges([
                Type::EMPLOYEUR => 'secondary',
                Type::PARTICIPANT => 'primary',
            ])
            ->setDisabled();

        yield TextField::new('prenom', 'Prénom');
        yield TextField::new('nomFamille', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TextField::new('numeroTelephone', 'Téléphone')
            ->hideOnIndex();
        yield TextField::new('commune', $renderProFields ? 'Adresse lieu d\'immersion' : 'Commune')
            ->hideOnIndex();

        if ($renderProFields) {
            yield FormField::addPanel('Informations Entreprise')
                ->hideOnIndex()
            ;
            yield TextField::new('nomEntreprise', 'Nom')->hideOnIndex();
            yield TextField::new('secteurActivite', 'Secteur d\'activité')->hideOnIndex();
            yield TextField::new('nombreEmployes', 'Effectif')->hideOnIndex();
            yield TextField::new('prenomTuteur', 'Prénom Tuteur·ice')->hideOnIndex();
            yield TextField::new('nomFamilleTuteur', 'Nom Tuteur·ice')->hideOnIndex();
        } else {
            yield ChoiceField::new('situation', 'Situation')
                ->setChoices([
                    'Salarié' => Contact::SALARIE,
                    'Demandeur d\'emploi' => Contact::DEMANDEUR_EMPLOI,
                    'Scolaire / Étudiant·e' => Contact::SCOLAIRE_ETUDIANT,
                    'Autre' => Contact::AUTRE,
                ])
                ->hideOnIndex();

            yield ChoiceField::new('trancheAge', 'Tranche d\'âge')
                ->setChoices([
                    'Plus de 18 ans' => Contact::MAJEUR,
                    'Moins de 18 ans' => Contact::MINEUR,
                ])
                ->hideOnIndex();

            yield FormField::addPanel('Référent·e (Optionnel)')
                ->hideOnIndex();

            yield AssociationField::new('referent', 'Référent·e')
                ->autocomplete()
                ->setHelp('Sélectionner ici le·la référent·e à associer, à partir des informations saisies ci-dessous par le·la participant·e.')
                ->hideOnIndex();

            yield TextField::new('nomReferent', 'Nom Référent·e (Saisi)')->hideOnIndex();
            yield TextField::new('prenomReferent', 'Prénom Référent·e (Saisi)')->hideOnIndex();
            yield TextField::new('structureReferent', 'Structure Référent·e (Saisie)')->hideOnIndex();
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->disable(Action::NEW)

            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-pen')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Modifier']);
            })

            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Supprimer']);
            })
        ;
    }
}
