<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Referent;
use App\Entity\Roles;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le·la référent·e')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du référent·e')
            ->setEntityLabelInSingular('Référent·e')
            ->setEntityLabelInPlural('Référent·es')
            ->setDefaultSort(['nomFamille' => 'ASC'])
            ->showEntityActionsInlined()
            ->setEntityPermission(Roles::ADMIN)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('utilisateur', 'Compte utilisateur associé')
            ->setFormTypeOption('choice_label', 'email')
            ->setQueryBuilder(function (QueryBuilder $queryBuilder) {
                $ids = $queryBuilder->getEntityManager()->getConnection()
                    ->executeQuery(
                        'SELECT id FROM "user" WHERE CAST(roles AS text) LIKE :role',
                        ['role' => '%"'.Roles::REFERENT.'"%']
                    )
                    ->fetchFirstColumn();

                return $queryBuilder
                    ->andWhere('entity.id IN (:ids)')
                    ->setParameter('ids', !empty($ids) ? $ids : [0]);
            })
            ->hideOnIndex();

        yield TextField::new('prenom', 'Prénom');
        yield TextField::new('nomFamille', 'Nom');
        yield EmailField::new('email', 'Email');
        yield TextField::new('telephone', 'Téléphone')
            ->formatValue(fn ($v) => $v ?: '—');

        yield TextField::new('fonction', 'Fonction')
            ->formatValue(fn ($v) => $v ?: '—');

        yield TextField::new('structure', 'Structure')
            ->formatValue(fn ($v) => $v ?: '—');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions

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
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }
}
