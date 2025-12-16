<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ModelQuestionnaire;
use App\Entity\Session;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SessionFixtures extends Fixture implements DependentFixtureInterface
{
    public const SESSION_OUVERTE_REF = 'session-ouverte';

    public function load(ObjectManager $manager): void
    {
        $model = $this->getReference(ModelQuestionnaireFixtures::MODEL_REF, ModelQuestionnaire::class);

        $session = new Session();
        $session->setNom('Session Juin 2025 - Nantes');
        $session->setDateDebut(new \DateTime('first day of this month'));
        $session->setDateFin(new \DateTime('last day of this month'));
        $session->setStatut(Session::STATUT_OUVERT);
        $session->setModelQuestionnaire($model);

        $manager->persist($session);
        $this->addReference(self::SESSION_OUVERTE_REF, $session);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ModelQuestionnaireFixtures::class,
        ];
    }
}
