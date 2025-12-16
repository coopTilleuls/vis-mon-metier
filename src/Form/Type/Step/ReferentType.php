<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Form\Data\Step\ReferentDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ReferentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label' => 'Email référent·e',
            'required' => false,
        ]);
        $builder->add('prenom', TextType::class, [
            'label' => 'Prenom référent·e',
            'required' => false,
        ]);
        $builder->add('nomFamille', TextType::class, [
            'label' => 'Nom référent·e',
            'required' => false,
        ]);
        $builder->add('fonction', TextType::class, [
            'label' => 'Fonction référent·e',
            'required' => false,
        ]);
        $builder->add('telephone', TextType::class, [
            'label' => 'Telephone référent·e',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReferentDto::class,
        ]);
    }
}
