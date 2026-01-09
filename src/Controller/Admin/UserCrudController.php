<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Roles;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur·rice')
            ->setEntityLabelInPlural('Utilisateur·rices')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des utilisateur·ices')
            ->setDefaultSort(['id' => 'DESC'])
            ->setEntityPermission(Roles::ADMIN)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::DETAIL)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
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
            ->setPermission(Action::NEW, Roles::ADMIN)
            ->setPermission(Action::EDIT, Roles::ADMIN)
            ->setPermission(Action::DELETE, Roles::ADMIN)
            ->setPermission(Action::DETAIL, Roles::ADMIN);
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', 'Email');

        yield TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'required' => Crud::PAGE_NEW === $pageName,
                'empty_data' => '',
                'attr' => ['autocomplete' => 'new-password'],
                'mapped' => false,
            ])
            ->onlyOnForms()
            ->setHelp(Crud::PAGE_EDIT === $pageName ? 'Laissez vide pour conserver le mot de passe actuel' : '');

        yield ChoiceField::new('roles', 'Rôles')
            ->setChoices([
                'Référent·e' => Roles::REFERENT,
                'Lecteur·rice' => Roles::LECTEUR,
                'Administrateur·ice' => Roles::ADMIN,
            ])
            ->allowMultipleChoices()
            ->renderAsBadges([
                Roles::ADMIN => 'danger',
                Roles::REFERENT => 'info',
                Roles::LECTEUR => 'secondary',
            ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handlePassword(User $user): void
    {
        $request = $this->getContext()?->getRequest();
        $formData = $request->request->all('User');
        $plainPassword = $formData['password'] ?? null;

        if (!empty($plainPassword)) {
            $hashedPassword = $this->userPasswordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }
    }
}
