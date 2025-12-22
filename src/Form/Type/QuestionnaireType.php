<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Controller\QuestionnaireController;
use App\Entity\Session;
use App\Form\Dto\Questionnaire;
use App\Form\Type\Step\ContactType;
use App\Form\Type\Step\ReponsesDispositionType;
use App\Form\Type\Step\ReponsesScoreType;
use Symfony\Component\Form\Flow\AbstractFlowType;
use Symfony\Component\Form\Flow\FormFlowBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class QuestionnaireType extends AbstractFlowType
{
    public function buildFormFlow(FormFlowBuilderInterface $builder, array $options): void
    {
        $session = $options[QuestionnaireController::SESSION_ENTITY] ?? null;
        $isPro = $options[QuestionnaireController::IS_PRO] ?? false;

        $builder
            ->addStep('contact', ContactType::class)
            ->addStep('reponsesScore', ReponsesScoreType::class, [
                QuestionnaireController::SESSION_ENTITY => $session,
                QuestionnaireController::IS_PRO => $isPro,
            ])
            ->addStep('reponsesDisposition', ReponsesDispositionType::class, [
                QuestionnaireController::SESSION_ENTITY => $session,
                QuestionnaireController::IS_PRO => $isPro,
            ])
            ->add('navigator', QuestionnaireNavigatorType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Questionnaire::class,
            'step_property_path' => 'currentStep',
            QuestionnaireController::SESSION_ENTITY => null,
            QuestionnaireController::IS_PRO => null,
        ]);
        $resolver->setAllowedTypes(QuestionnaireController::SESSION_ENTITY, ['null', Session::class]);
        $resolver->setAllowedTypes(QuestionnaireController::IS_PRO, 'bool');
    }
}
