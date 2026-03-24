<?php

declare(strict_types=1);

namespace App\Form\Type\Step;

use App\Controller\QuestionnaireController;
use App\Entity\Session;
use App\Form\Dto\Step\ReponsesScore;
use App\Form\JsonEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ReponsesScoreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isPro = $options[QuestionnaireController::IS_PRO];
        $questionsData = [];
        if ($session = $options[QuestionnaireController::SESSION_ENTITY]) {
            /** @var Session $session */
            $questionsData = $session->getModelQuestionnaire()->getQuestionsScore();
        }
        $jsonSchema = $this->generateJsonSchema($questionsData, $isPro);

        $builder->add('reponses', JsonEditorType::class, [
            'json_schema' => json_encode($jsonSchema),
            'error_bubbling' => true,
            'editor_options' => [
                'theme' => 'bootstrap5',
                'disable_edit_json' => true,
                'disable_properties' => true,
                'disable_collapse' => true,
                'no_additional_properties' => true,
                'disable_array_add' => true,
                'disable_array_delete' => true,
                'disable_array_reorder' => true,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReponsesScore::class,
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
            'title' => $data['titre'] ?? 'Questions Score',
            'properties' => [],
            'required' => [],
        ];

        if (empty($data['questions'])) {
            return $schema;
        }

        foreach ($data['questions'] as $index => $question) {
            $key = 'question_'.$index;

            $label = $isPro
                ? ($question['label_employeur'] ?? "Question $index")
                : ($question['label_participant'] ?? "Question $index");

            $enums = [];
            $enumTitles = [];

            foreach ($question['reponses_possibles'] ?? [] as $reponse) {
                $enums[] = (string) $reponse['valeur'];
                $enumTitles[] = $isPro
                    ? ($reponse['libelle_employeur'] ?? $reponse['libelle'] ?? '')
                    : ($reponse['libelle_participant'] ?? $reponse['libelle'] ?? '');
            }

            $schema['properties'][$key] = [
                'type' => 'string',
                'title' => $label,
                'enum' => $enums,
                'format' => 'radio',
                'minLength' => 1,
                'default' => '',
                'options' => [
                    'enum_titles' => $enumTitles,
                ],
            ];

            $schema['required'][] = $key;
        }

        return $schema;
    }
}
