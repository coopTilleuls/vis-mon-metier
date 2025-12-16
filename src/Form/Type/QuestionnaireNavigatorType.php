<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Flow\FormFlowCursor;
use Symfony\Component\Form\Flow\Type\FinishFlowType;
use Symfony\Component\Form\Flow\Type\NextFlowType;
use Symfony\Component\Form\Flow\Type\PreviousFlowType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class QuestionnaireNavigatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('back', PreviousFlowType::class, [
                'label' => 'Précédent',
                'include_if' => fn (FormFlowCursor $cursor) => !$cursor->isFirstStep(),
                'validate' => false,
                'validation_groups' => false,
                'clear_submission' => false,
            ])
            ->add('next', NextFlowType::class, [
                'label' => 'Suivant',
                'include_if' => fn (FormFlowCursor $cursor) => !$cursor->isLastStep(),
            ])
            ->add('finish', FinishFlowType::class, [
                'label' => 'Envoyer',
                'include_if' => fn (FormFlowCursor $cursor) => $cursor->isLastStep(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
            'mapped' => false,
            'priority' => -100,
        ]);
    }
}
