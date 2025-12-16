<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Controller\QuestionnaireController;
use App\Entity\Contact;
use App\Form\Data\Step\ContactDto;
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
                'required' => false,
            ])
            ->add('secteurActivite', TextType::class, [
                'required' => false,
            ])
            ->add('type', TextType::class, [
                'required' => false,
            ])
            ->add('nombreEmployes', TextType::class, [
                'required' => false,
            ])
            ->add('metier', TextType::class, [
                'required' => false,
            ])
            ->add('prenom', TextType::class)
            ->add('nomFamille', TextType::class)
            ->add('numeroTelephone', TextType::class, [
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
                'choices' => [
                    'Plus de 18 ans' => Contact::MAJEUR,
                    'Moins de 18 ans' => Contact::MINEUR,
                ],
                'required' => false,
                'placeholder' => 'Sélectionnez votre tranche d\'âge',
            ])
            ->add('email', EmailType::class)
            ->add('commune', TextType::class)
            ->add('referent', ReferentType::class, [
                'required' => false,
            ])
        ;

        if (!$isPro) {
            $builder->add('referent', ReferentType::class, [
                'required' => false,
                'label' => false,
            ]);
        }
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
