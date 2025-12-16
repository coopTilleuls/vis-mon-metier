<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class JsonEditorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($dataAsArray) {
                if (empty($dataAsArray)) {
                    return '{}';
                }

                return json_encode($dataAsArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            },

            function ($dataAsString) {
                if (empty($dataAsString)) {
                    return [];
                }

                return json_decode($dataAsString, true);
            }
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['json_schema'] = $options['json_schema'];
        $view->vars['editor_options'] = json_encode($options['editor_options']);
        $view->vars['editor_container_id'] = 'json_editor_container_'.$view->vars['id'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'json_schema' => '{}',
            'editor_options' => [
                'theme' => 'bootstrap5',
                'iconlib' => 'fontawesome5',
                'disable_edit_json' => true,
                'disable_properties' => true,
                'disable_collapse' => true,
                'no_additional_properties' => true,
                'required_by_default' => false,
            ],
        ]);

        $resolver->setAllowedTypes('json_schema', 'string');
        $resolver->setAllowedTypes('editor_options', 'array');
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'json_schema_editor';
    }
}
