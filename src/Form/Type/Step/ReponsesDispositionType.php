<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Controller\QuestionnaireController;
use App\Entity\Session;
use App\Form\Data\Step\ReponsesDispositionDto;
use App\Form\JsonEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ReponsesDispositionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isPro = $options[QuestionnaireController::IS_PRO];
        $questionsData = [];
        if ($session = $options[QuestionnaireController::SESSION_ENTITY]) {
            /** @var Session $session */
            $questionsData = $session->getModelQuestionnaire()->getQuestionsDisposition();
        }
        $jsonSchema = $this->generateJsonSchema($questionsData, $isPro);

        $builder->add('reponses', JsonEditorType::class, [
            'json_schema' => json_encode($jsonSchema),
            'editor_options' => [
                'theme' => 'bootstrap5',
                'disable_edit_json' => true,
                'disable_properties' => true,
                'disable_collapse' => true,
                'no_additional_properties' => true,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReponsesDispositionDto::class,
            QuestionnaireController::SESSION_ENTITY => null,
            QuestionnaireController::IS_PRO => false,
        ]);
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<mixed>
     */
    private function generateJsonSchema(array $data, bool $isPro): array
    {
        $schema = [
            'type' => 'object',
            'title' => $data['titre'] ?? 'Questions Disposition',
            'properties' => [],
            'required' => [],
        ];

        if (empty($data['questions'])) {
            return $schema;
        }

        foreach ($data['questions'] as $index => $question) {
            $key = 'question_'.$index;
            $typeReponse = $question['type_reponse'] ?? 'text';

            $label = $isPro
                ? ($question['label_employeur'] ?? "Question $index")
                : ($question['label_participant'] ?? "Question $index");

            $property = [
                'title' => $label,
            ];

            $schema['required'][] = $key;

            switch ($typeReponse) {
                case 'choix unique':
                    $property['type'] = 'string';
                    $property['format'] = 'radio';
                    [$enums, $titles] = $this->extractOptions($question['reponses_possibles'] ?? []);
                    $property['enum'] = $enums;
                    $property['options'] = ['enum_titles' => $titles];
                    break;

                case 'choix multiples':
                    $property['type'] = 'array';
                    $property['format'] = 'checkbox';
                    $property['uniqueItems'] = true;
                    [$enums, $titles] = $this->extractOptions($question['reponses_possibles'] ?? []);

                    $property['items'] = [
                        'type' => 'string',
                        'enum' => $enums,
                        'options' => ['enum_titles' => $titles],
                    ];
                    break;

                case 'réponse libre':
                default:
                    $property['type'] = 'string';
                    $property['format'] = 'textarea';
                    $property['options'] = [
                        'input_attributes' => ['rows' => 3],
                    ];

                    if (!empty($question['config_texte_libre'])) {
                        $property['options']['input_attributes']['placeholder'] = $question['config_texte_libre'];
                    }
                    break;
            }

            $schema['properties'][$key] = $property;
        }

        return $schema;
    }

    /**
     * @param array<mixed> $reponses
     *
     * @return array<mixed>
     */
    private function extractOptions(array $reponses): array
    {
        $enums = [];
        $titles = [];
        foreach ($reponses as $rep) {
            $enums[] = (string) $rep['valeur'];
            $titles[] = $rep['libelle'];
        }

        return [$enums, $titles];
    }
}
