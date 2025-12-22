<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use App\Entity\Referent;
use App\ValueObject\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ContactFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONTACT_EMPLOYEUR_REF = 'contact-employeur';
    public const CONTACT_PARTICIPANT_REF = 'contact-participant';

    public function load(ObjectManager $manager): void
    {
        $contact = new Contact();
        $contact->setType(Type::PARTICIPANT);
        $contact->setPrenom('Jean');
        $contact->setNomFamille('Martin');
        $contact->setEmail('jean.martin@gmail.com');
        $contact->setNumeroTelephone('0612345678');
        $contact->setCommune('Blain');
        $contact->setSituation(Contact::DEMANDEUR_EMPLOI);
        $contact->setTrancheAge(Contact::MAJEUR);
        $contact->setReferent(new Referent());

        $manager->persist($contact);
        $this->addReference(self::CONTACT_PARTICIPANT_REF, $contact);

        $contactPro = new Contact();
        $contactPro->setType(Type::EMPLOYEUR);
        $contactPro->setNomEntreprise('Peugeot');
        $contactPro->setSecteurActivite('Industrie');
        $contactPro->setPrenom('Paul');
        $contactPro->setNomFamille('Dupont');
        $contactPro->setEmail('paul.dupont@gmail.com');
        $contactPro->setNumeroTelephone('0712345678');
        $contactPro->setCommune('10 rue de la Plée 44000 NANTES');
        $contactPro->setNombreEmployes('10-50');
        $contactPro->setPrenomTuteur('Emeline');
        $contactPro->setNomFamilleTuteur('Marchand');

        $manager->persist($contactPro);
        $this->addReference(self::CONTACT_EMPLOYEUR_REF, $contactPro);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ReferentFixtures::class,
        ];
    }
}
