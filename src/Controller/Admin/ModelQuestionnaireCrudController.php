<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\EasyAdmin\Field\JsonField;
use App\Entity\ModelQuestionnaire;
use App\Entity\Roles;
use App\Form\JsonEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            ->showEntityActionsInlined()
            ->setEntityPermission(Roles::ADMIN)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('nom', 'Nom');

        yield ArrayField::new('communes', 'Liste des communes proposées')
            ->setHelp('Ne pas ajouter « Autre » à la liste : cette option est présente par défaut.');

        yield BooleanField::new('active', 'Actif')
            ->renderAsSwitch(false);

        $autosaveUrl = null;
        if (Crud::PAGE_EDIT === $pageName) {
            $model = $this->getContext()?->getEntity()->getInstance();
            if ($model instanceof ModelQuestionnaire && $model->getId()) {
                $autosaveUrl = $this->generateUrl('admin_model_questionnaire_autosave', ['id' => $model->getId()]);
            }
        }

        if ($questionScoreFormSchema = file_get_contents(__DIR__.'/../../jsonschema/questionsScoreFormSchema.json')) {
            yield JsonField::new('questionsScore')
                ->setLabel(false)
                ->setJsonSchema($questionScoreFormSchema)
                ->setFormType(JsonEditorType::class)
                ->setFormTypeOptions([
                    'autosave_url' => $autosaveUrl,
                    'field_name' => 'questionsScore',
                ])
                ->setRequired(true)
            ;
        }

        if ($questionDispositionSchema = file_get_contents(__DIR__.'/../../jsonschema/questionsDispositionFormSchema.json')) {
            yield JsonField::new('questionsDisposition')
                ->setLabel(false)
                ->setJsonSchema($questionDispositionSchema)
                ->setFormType(JsonEditorType::class)
                ->setFormTypeOptions([
                    'autosave_url' => $autosaveUrl,
                    'field_name' => 'questionsDisposition',
                ])
                ->setRequired(true)
            ;
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
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

            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Ajouter un questionnaire');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel('Sauvegarder et quitter');
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setLabel('Sauvegarder et continuer')
                    ->setIcon(null);
            })
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::DETAIL)
        ;
    }
}
