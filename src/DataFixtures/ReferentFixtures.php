<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Referent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReferentFixtures extends Fixture
{
    public const REFERENT_REF = 'referent';

    public function load(ObjectManager $manager): void
    {
        $referent = new Referent();
        $referent->setPrenom('Alice');
        $referent->setNomFamille('Dubois');
        $referent->setEmail('alice.dubois@mission-locale.fr');
        $referent->setTelephone('0240123456');
        $referent->setFonction('Conseillère');
        $referent->setStructure('Mission Locale');

        $manager->persist($referent);
        $this->addReference(self::REFERENT_REF, $referent);

        $manager->flush();
    }
}
