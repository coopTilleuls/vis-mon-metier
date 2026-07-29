<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Roles;
use App\Entity\User;
use App\Security\Voter\ContactVoter;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

/**
 * @extends AbstractCrudController<Contact>
 */
class ContactCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Contact::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        if ($this->isGranted(Roles::REFERENT) && !$this->isGranted(Roles::ADMIN)) {
            $crud->setDefaultSort(['type' => 'DESC', 'id' => 'DESC']);
        } else {
            $crud->setDefaultSort(['id' => 'DESC']);
        }

        return $crud
            ->setEntityLabelInSingular('Contact')
            ->setEntityLabelInPlural('Contacts')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des contacts')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Contact $contact) => (string) $contact)
            ->showEntityActionsInlined()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', 'Type')
                ->setChoices(Type::choiceOptions()))
            ->add(TextFilter::new('email', 'Mail'))
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        if ($this->isGranted(Roles::ADMIN)) {
            return $qb;
        }

        if ($this->isGranted(Roles::REFERENT)) {
            /** @var User $user */
            $user = $this->getUser();
            $referentIds = $user->getReferents()->map(fn ($r) => $r->getId())->toArray();

            if (empty($referentIds)) {
                $qb->andWhere('entity.type = :typeEmployeur')
                    ->setParameter('typeEmployeur', Type::EMPLOYEUR);
            } else {
                $qb->andWhere($qb->expr()->orX(
                    'entity.referent IN (:referentIds)',
                    'entity.type = :typeEmployeur'
                ))
                    ->setParameter('referentIds', $referentIds)
                    ->setParameter('typeEmployeur', Type::EMPLOYEUR);
            }
        }

        return $qb;
    }

    public function configureFields(string $pageName): iterable
    {
        /** @var ?Contact $contact */
        $contact = $this->getContext()?->getEntity()->getInstance();
        $renderProFields = Type::EMPLOYEUR === $contact?->getType();

        $activeModelQuestionnaire = $this->entityManager->getRepository(ModelQuestionnaire::class)->findOneBy(['active' => true]);
        $communeChoices = [];
        if ($activeModelQuestionnaire) {
            foreach ($activeModelQuestionnaire->getCommunes() as $communeName) {
                $communeChoices[$communeName] = $communeName;
            }
        }

        yield FormField::addPanel('Coordonnées du signataire de la convention de stage');
        yield ChoiceField::new('type', 'Type')
            ->setChoices(Type::choiceOptions())
            ->renderAsBadges([
                Type::EMPLOYEUR => 'secondary',
                Type::PARTICIPANT => 'primary',
            ])
            ->setDisabled();

        yield TextField::new('prenom', 'Prénom');
        yield TextField::new('nomFamille', 'Nom');
        if ($renderProFields) {
            yield TextField::new('fonctionSignataire', 'Fonction signataire')->hideOnIndex();
        }
        yield EmailField::new('email', 'Email');
        yield TextField::new('numeroTelephone', 'Téléphone')
            ->hideOnIndex();

        if (!$renderProFields) {
            yield TextField::new('commune', 'Commune')
                ->hideOnIndex();
        }

        if ($renderProFields) {
            yield FormField::addPanel('Informations Entreprise')
                ->hideOnIndex();
            yield TextField::new('nomEntreprise', 'Nom')->hideOnIndex();
            yield TextField::new('secteurActivite', 'Secteur d\'activité')->hideOnIndex();
            yield ChoiceField::new('nombreEmployes', 'Effectif')
                ->setChoices([
                    '- de 10 salarié·e·s' => '- de 10 salarié·e·s',
                    'Entre 11 et 50 salarié·e·s' => 'Entre 11 et 50 salarié·e·s',
                    'Entre 51 et 250 salarié·e·s' => 'Entre 51 et 250 salarié·e·s',
                    '+ de 250 salarié·e·s' => '+ de 250 salarié·e·s',
                ])
                ->hideOnIndex();
            yield TextField::new('siret', 'SIRET')->hideOnIndex();
            yield TextField::new('commune', 'Commune du lieu d\'immersion')
                ->hideOnIndex()
                ->onlyOnDetail();
            yield ChoiceField::new('commune', 'Commune du lieu d\'immersion')
                ->setChoices($communeChoices)
                ->hideOnIndex()
                ->onlyOnForms()
                ->setHelp('Commune sélectionnée');
            yield TextField::new('adresseEntreprise', 'Adresse entreprise')->hideOnIndex();
            yield FormField::addPanel('Tuteur ou tutrice pendant l\'immersion')->hideOnIndex();
            yield TextField::new('prenomTuteur', 'Prénom')->hideOnIndex();
            yield TextField::new('nomFamilleTuteur', 'Nom')->hideOnIndex();
            yield TextField::new('fonctionTuteur', 'Fonction')->hideOnIndex();
            yield EmailField::new('emailTuteur', 'Email')->hideOnIndex();
            yield TextField::new('telephoneTuteur', 'Téléphone')->hideOnIndex();

            yield FormField::addPanel('Mise en place administrative')->hideOnIndex();
            yield TextField::new('horaires', 'Horaires du stage')->hideOnIndex();
            yield BooleanField::new('epiDisponible', 'EPI disponible')
                ->hideOnIndex();
            yield BooleanField::new('autresMesuresPrevention', 'Autres mesures de prévention')
                ->hideOnIndex();
            yield TextareaField::new('precisionMesuresPrevention', 'Précision mesures de prévention')
                ->hideOnIndex();
        } else {
            yield ChoiceField::new('situation', 'Situation')
                ->setChoices([
                    'Salarié' => Contact::SALARIE,
                    'Fonctionnaire' => Contact::FONCTIONNAIRE,
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

            if (Crud::PAGE_DETAIL === $pageName || $this->isGranted(Roles::ADMIN)) {
                yield FormField::addPanel('Référent·e (Optionnel)')
                    ->hideOnIndex();

                yield AssociationField::new('referent', 'Référent·e')
                    ->autocomplete()
                    ->setHelp('Sélectionner ici le·la référent·e à associer')
                    ->hideOnIndex();
            }
        }

        yield FormField::addPanel('Consentement RGPD')->onlyOnDetail();
        yield BooleanField::new('consentementRgpd', 'Consentement donné')
            ->onlyOnDetail()
            ->renderAsSwitch(false);
        yield DateTimeField::new('consentementRgpdAt', 'Date du consentement')
            ->onlyOnDetail()
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->disable(Action::NEW)

            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa-solid fa-eye')
                    ->setLabel(false)
                    ->setHtmlAttributes(['title' => 'Consulter']);
            })
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

            ->setPermission(Action::DETAIL, ContactVoter::VIEW)
            ->setPermission(Action::EDIT, ContactVoter::EDIT)
            ->setPermission(Action::DELETE, ContactVoter::DELETE)
            ->setPermission(Action::BATCH_DELETE, Roles::ADMIN)

            ->reorder(Crud::PAGE_INDEX, [Action::DETAIL, Action::EDIT, Action::DELETE])
        ;
    }
}
