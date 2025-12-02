<?php

namespace App\Controller\Admin;

use App\Entity\ModelQuestionnaire;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

/**
 * @extends AbstractCrudController<ModelQuestionnaire>
 */
class ModelQuestionnaireCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ModelQuestionnaire::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des questionnaires')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un questionnaire')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le questionnaire')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du questionnaire')
            ->setEntityLabelInSingular('Questionnaire')
            ->setEntityLabelInPlural('Questionnaires')
            ->setDefaultSort(['id' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield BooleanField::new('active', 'Actif')
            ->renderAsSwitch(false);

        yield ArrayField::new('questionsDisposition', 'Questions disposition')
            ->setRequired(true);

        yield ArrayField::new('QuestionsScore', 'Questions score de compatibilité')
            ->setRequired(true);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un questionnaire');
            });
    }
}
