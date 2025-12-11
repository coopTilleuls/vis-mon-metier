<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\JsonField;
use App\Entity\ModelQuestionnaire;
use App\Form\JsonEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
            ->setPageTitle(Crud::PAGE_DETAIL, 'Questionnaire')
            ->setEntityLabelInSingular('Questionnaire')
            ->setEntityLabelInPlural('Questionnaires')
            ->setDefaultSort(['id' => 'DESC'])
            ->addFormTheme('easyadmin/json_field.html.twig')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'id')
        ->onlyOnIndex();

        yield BooleanField::new('active', 'Actif')
            ->renderAsSwitch(false);

        if ($questionScoreFormSchema = file_get_contents(__DIR__.'/../../jsonschema/questionsScoreFormSchema.json')) {
            yield JsonField::new('questionsScore')
                ->setLabel(false)
                ->setJsonSchema($questionScoreFormSchema)
                ->setFormType(JsonEditorType::class)
                ->setRequired(true)
            ;
        }

        if ($questionDispositionSchema = file_get_contents(__DIR__.'/../../jsonschema/questionsDispositionFormSchema.json')) {
            yield JsonField::new('questionsDisposition')
                ->setLabel(false)
                ->setJsonSchema($questionDispositionSchema)
                ->setFormType(JsonEditorType::class)
                ->setRequired(true)
            ;
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un questionnaire');
            })
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }
}
