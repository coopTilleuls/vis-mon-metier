<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Questionnaire;
use App\ValueObject\Type;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

/**
 * @extends AbstractCrudController<Questionnaire>
 */
class QuestionnaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Questionnaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Questionnaire')
            ->setEntityLabelInPlural('Questionnaires')
            ->setPageTitle('index', 'Liste des questionnaires remplis')
            ->setDefaultSort(['createdAt' => 'DESC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $editAnswers = Action::new('editAnswers', 'Modifier Réponses')
            ->linkToRoute('admin_questionnaire_edit_answers', function (Questionnaire $questionnaire): array {
                return ['id' => $questionnaire->getId()];
            })
            ->setIcon('fa fa-edit');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $editAnswers)
            ->add(Crud::PAGE_DETAIL, $editAnswers)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->disable(Action::NEW, Action::EDIT)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('session', 'Session')
            ->autocomplete()
            ->setRequired(true)
            ->hideOnForm();

        yield AssociationField::new('contact', 'Contact')
            ->setRequired(true)
            ->hideOnForm();

        yield ChoiceField::new('type', 'Type de profil')
            ->setChoices(Type::choiceOptions())
            ->renderAsBadges([
                Type::EMPLOYEUR => 'success',
                Type::PARTICIPANT => 'primary',
            ])
            ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add('session')
            ->add(
                ChoiceFilter::new('type', 'Type de profil')
                    ->setChoices([
                        Type::EMPLOYEUR => Type::EMPLOYEUR,
                        Type::PARTICIPANT => Type::PARTICIPANT,
                    ])
            );
    }
}
