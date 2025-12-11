<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ModelQuestionnaire;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ModelQuestionnaireFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $contentDisposition = file_get_contents('questionsDisposition.json', true);
        $contentScore = file_get_contents('questionsDisposition.json', true);

        if (false === $contentDisposition || false === $contentScore) {
            throw new \RuntimeException('Cannot read setting file(s).');
        }

        $questionsDisposition = json_decode($contentDisposition, true);
        $questionsScore = json_decode($contentScore, true);

        $model = new ModelQuestionnaire();
        $model->setActive(true);
        $model->setQuestionsDisposition($questionsDisposition);
        $model->setQuestionsScore($questionsScore);

        $manager->persist($model);
        $manager->flush();
    }
}
