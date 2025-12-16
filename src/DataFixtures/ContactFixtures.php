<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContactFixtures extends Fixture
{
    public const CONTACT_EMPLOYEUR_REF = 'contact-employeur';
    public const CONTACT_PARTICIPANT_REF = 'contact-participant';

    public function load(ObjectManager $manager): void
    {
        $contact = new Contact();
        $contact->setType('participant');
        $contact->setPrenom('Jean');
        $contact->setNomFamille('Martin');
        $contact->setEmail('jean.martin@gmail.com');
        $contact->setNumeroTelephone('0612345678');
        $contact->setCommune('Blain');
        $contact->setSituation(Contact::DEMANDEUR_EMPLOI);
        $contact->setTrancheAge(Contact::MAJEUR);
        $this->addReference(self::CONTACT_PARTICIPANT_REF, $contact);

        $manager->persist($contact);

        $contactPro = new Contact();
        $contactPro->setType('employeur');
        $contactPro->setNomEntreprise('Peugeot');
        $contactPro->setSecteurActivite('Industrie');
        $contactPro->setPrenom('Paul');
        $contactPro->setNomFamille('Dupont');
        $contactPro->setEmail('paul.dupont@gmail.com');
        $contactPro->setNumeroTelephone('0712345678');
        $contactPro->setCommune('Blain');
        $contactPro->setNombreEmployes('10-50');

        $manager->persist($contactPro);
        $this->addReference(self::CONTACT_EMPLOYEUR_REF, $contact);

        $manager->flush();
    }
}
