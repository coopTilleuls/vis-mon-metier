<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Controller\QuestionnaireController;
use App\Entity\Contact;
use App\Form\Dto\Step\Contact as ContactDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isPro = $options[QuestionnaireController::IS_PRO];
        $builder
            ->add('nomEntreprise', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('secteurActivite', TextType::class, [
                'label' => 'Secteur d\'activité',
                'required' => true,
            ])
            ->add('nombreEmployes', TextType::class, [
                'label' => 'Nombre d\'employés',
                'required' => true,
            ])
            ->add('metier', TextType::class, [
                'label' => 'Métier proposé à l\'immersion',
                'required' => true,
            ])
            ->add('type', TextType::class, [
                'required' => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('nomFamille', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('numeroTelephone', TextType::class, [
                'label' => 'Numéro de téléphone',
                'required' => true,
            ])
            ->add('situation', ChoiceType::class, [
                'choices' => [
                    'Salarié' => Contact::SALARIE,
                    'Demandeur d\'emploi' => Contact::DEMANDEUR_EMPLOI,
                    'Scolaire / Etudiant' => Contact::SCOLAIRE_ETUDIANT,
                    'Autre' => Contact::AUTRE,
                ],
                'required' => true,
                'placeholder' => 'Sélectionnez votre situation',
            ])
            ->add('trancheAge', ChoiceType::class, [
                'label' => 'Tranche d\'âge',
                'choices' => [
                    'Plus de 18 ans' => Contact::MAJEUR,
                    'Moins de 18 ans' => Contact::MINEUR,
                ],
                'required' => true,
                'placeholder' => 'Sélectionnez votre tranche d\'âge',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => true,
            ])
            ->add('commune', TextType::class, [
                'label' => $isPro ? 'Adresse du lieu d\'immersion' : 'Commune',
                'required' => true,
            ])
            ->add('nomReferent', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('prenomReferent', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
            ])
            ->add('structureReferent', TextType::class, [
                'label' => 'Structure',
                'required' => false,
            ])
            ->add('prenomTuteur', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('nomFamilleTuteur', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDto::class,
            QuestionnaireController::IS_PRO => false,
        ]);

        $resolver->setAllowedTypes(QuestionnaireController::IS_PRO, 'bool');
    }
}
