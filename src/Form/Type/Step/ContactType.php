<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Controller\QuestionnaireController;
use App\Entity\Contact;
use App\Entity\Referent;
use App\Entity\Session;
use App\Form\Dto\Step\Contact as ContactDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ContactType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            if (!$data instanceof ContactDto || !$data->referent) {
                return;
            }

            if ($data->referent->getId()) {
                $managedReferent = $this->entityManager->getRepository(Referent::class)->find($data->referent->getId());
                if ($managedReferent) {
                    $data->referent = $managedReferent;
                }
            }
        });

        $isPro = $options[QuestionnaireController::IS_PRO];
        $session = $options[QuestionnaireController::SESSION_ENTITY];

        $communeChoices = [];
        if ($session instanceof Session) {
            $modelQuestionnaire = $session->getModelQuestionnaire();
            if ($modelQuestionnaire) {
                foreach ($modelQuestionnaire->getCommunes() as $communeName) {
                    $communeChoices[$communeName] = $communeName;
                }
            }
        }

        if (!$isPro) {
            $communeChoices['Autre'] = 'Autre';
        }

        $builder
            ->add('nomEntreprise', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('secteurActivite', TextType::class, [
                'label' => 'Secteur d\'activité',
                'required' => true,
            ])
            ->add('nombreEmployes', ChoiceType::class, [
                'label' => 'Nombre de salarié·e·s',
                'required' => true,
                'placeholder' => 'Sélectionnez une tranche',
                'choices' => [
                    '- de 10 salarié·e·s' => '- de 10 salarié·e·s',
                    'Entre 11 et 50 salarié·e·s' => 'Entre 11 et 50 salarié·e·s',
                    'Entre 51 et 250 salarié·e·s' => 'Entre 51 et 250 salarié·e·s',
                    '+ de 250 salarié·e·s' => '+ de 250 salarié·e·s',
                ],
            ])
            ->add('adresseEntreprise', TextType::class, [
                'label' => 'Adresse',
                'required' => true,
            ])
            ->add('metier', TextType::class, [
                'label' => 'Métier proposé à l\'immersion',
                'required' => true,
                'help' => '1 seul métier par réponse, possibilité de répondre pour un autre métier en fin de questionnaire',
            ])
            ->add('nombreImmersion', IntegerType::class, [
                'label' => 'Combien d\'immersions d\'une journée proposez-vous pour ce métier ?',
                'required' => true,
                'attr' => ['min' => 1],
            ])
            ->add('immersionMemeDate', ChoiceType::class, [
                'label' => 'Si vous proposez plusieurs journées d\'immersion, auront-elles lieu à la même date ?',
                'choices' => ['Oui' => '1', 'Non' => '0'],
                'expanded' => true,
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
                    Contact::SALARIE => Contact::SALARIE,
                    Contact::FONCTIONNAIRE => Contact::FONCTIONNAIRE,
                    Contact::DEMANDEUR_EMPLOI => Contact::DEMANDEUR_EMPLOI,
                    Contact::SCOLAIRE_ETUDIANT => Contact::SCOLAIRE_ETUDIANT,
                    Contact::AUTRE => Contact::AUTRE,
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
        ;

        $builder
            ->add('fonctionSignataire', TextType::class, [
                'label' => 'Fonction',
                'required' => true,
            ])
            ->add('commune', ChoiceType::class, [
                'label' => $isPro ? 'Commune du lieu d\'immersion' : 'Commune',
                'required' => true,
                'choices' => $communeChoices,
                'placeholder' => 'Sélectionnez une commune',
            ])
            ->add('communeAutre', TextType::class, [
                'label' => 'Précisez votre commune',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Nantes, Saint-Nazaire...',
                ],
            ])
            ->add('referent', EntityType::class, [
                'class' => Referent::class,
                'label' => 'Structure de votre référent·e',
                'required' => false,
                'placeholder' => 'Je ne connais pas mon référent',
                'choice_label' => 'structure',
            ])
            ->add('prenomTuteur', TextType::class, [
                'label' => 'Prénom',
                'required' => true,
            ])
            ->add('nomFamilleTuteur', TextType::class, [
                'label' => 'Nom',
                'required' => true,
            ])
            ->add('fonctionTuteur', TextType::class, [
                'label' => 'Fonction',
                'required' => true,
            ])
            ->add('emailTuteur', EmailType::class, [
                'label' => 'Adresse e-mail (optionnel)',
                'required' => false,
            ])
            ->add('telephoneTuteur', TelType::class, [
                'label' => 'Numéro de téléphone (optionnel)',
                'required' => false,
                'attr' => ['placeholder' => '06 12 34 56 78'],
            ])
            ->add('siret', TextType::class, [
                'label' => 'Numéro SIRET',
                'required' => true,
                'attr' => [
                    'maxlength' => 14,
                ],
            ])
            ->add('horaires', TextType::class, [
                'label' => 'Horaires de l\'immersion',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ex: Lundi-Vendredi 8h-17h',
                ],
            ])
            ->add('epiDisponible', ChoiceType::class, [
                'label' => 'Mise à disposition d\'équipement de protection individuelle (EPI)',
                'choices' => ['Oui' => '1', 'Non' => '0'],
                'expanded' => true,
                'required' => true,
            ])
            ->add('autresMesuresPrevention', ChoiceType::class, [
                'label' => 'Présence d\'autres mesures de prévention',
                'choices' => ['Oui' => '1', 'Non' => '0'],
                'expanded' => true,
                'required' => true,
            ])
            ->add('precisionMesuresPrevention', TextareaType::class, [
                'label' => 'Précisez les autres mesures de prévention',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDto::class,
            QuestionnaireController::IS_PRO => false,
            QuestionnaireController::SESSION_ENTITY => null,
        ]);

        $resolver->setAllowedTypes(QuestionnaireController::IS_PRO, 'bool');
        $resolver->setAllowedTypes(QuestionnaireController::SESSION_ENTITY, ['null', Session::class]);
    }
}
