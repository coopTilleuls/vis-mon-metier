<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Questionnaire;
use App\Entity\Session;
use App\ValueObject\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class QuestionnaireFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $session = $this->getReference(SessionFixtures::SESSION_OUVERTE_REF, Session::class);
        $contact = $this->getReference(ContactFixtures::CONTACT_PARTICIPANT_REF, Contact::class);

        $questionnaire = new Questionnaire();
        $questionnaire->setSession($session);
        $questionnaire->setContact($contact);
        $questionnaire->setType(Type::PARTICIPANT);
        $dateTime = new \DateTimeImmutable();
        $questionnaire->setCreatedAt($dateTime);

        $contentScore = file_get_contents('reponsesScore.json', true);
        $contentDisposition = file_get_contents('reponsesDisposition.json', true);

        if (false === $contentScore || false === $contentDisposition) {
            throw new \RuntimeException('Cannot read setting file(s).');
        }

        $questionnaire->setReponsesScore(json_decode($contentScore, true));
        $questionnaire->setReponsesDisposition(json_decode($contentDisposition, true));

        $manager->persist($questionnaire);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SessionFixtures::class,
            ContactFixtures::class,
        ];
    }
}
