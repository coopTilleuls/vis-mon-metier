<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Appariement;
use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\Session;
use App\Entity\User;
use App\ValueObject\StatutImmersion;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Self-contained deterministic dataset for manual QA of the VMM tickets.
 * Reads no external file: everything is generated in-code so it can be run
 * on any checkout. Purges questionnaire-related data first (users are kept).
 */
#[AsCommand(
    name: 'app:load-test-fixtures',
    description: 'Loads a self-contained deterministic dataset for manual QA (sessions, questionnaires, matchings).',
)]
class LoadTestFixturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        #[Autowire('%kernel.environment%')]
        private readonly string $env = 'dev',
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('bulk', null, InputOption::VALUE_REQUIRED, 'Nombre d\'immersions supplémentaires à générer dans la session ouverte (test de charge).', '0');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Loading VMM test fixtures');

        // Garde-fou : cette commande PURGE les données — jamais en production.
        if ('prod' === $this->env) {
            $io->error('Commande de test interdite en production (purge de données).');

            return Command::FAILURE;
        }

        $this->purge($io);

        $model = $this->createModel();
        $referent = $this->createReferent();

        // --- Open session with full data (VMM-2 access gating, VMM-3 matchings) ---
        $open = $this->createSession('Session Ouverte', '2025-06-01', '2025-06-30', Session::STATUT_OUVERT, $model);
        // Session-audience flags only exist on the sessions branch — call them
        // through the dynamic helper so the command stays portable across branches.
        $this->callIfExists($open, 'setInscriptionVolontaireOuverte', true);
        $this->callIfExists($open, 'setInscriptionEntrepriseOuverte', true);

        $participants = [
            $this->participant($open, 'Léa', 'Martin', 'Salarié·e', 'Plus de 18 ans', 'Blain', $referent),
            $this->participant($open, 'Hugo', 'Bernard', "Demandeur·euse d'emploi", 'Plus de 18 ans', 'Bouvron', $referent),
            $this->participant($open, 'Manon', 'Petit', 'Scolaire, Etudiant·e', 'Moins de 18 ans', 'Le Gâvre', null),
            $this->participant($open, 'Nael', 'Robert', 'Autre', 'Plus de 18 ans', 'La Chevallerais', null),
            $this->participant($open, 'Jade', 'Richard', 'Salarié·e', 'Plus de 18 ans', 'Blain', $referent),
            $this->participant($open, 'Sacha', 'Durand', 'Scolaire, Etudiant·e', 'Moins de 18 ans', 'Autre', null),
            $this->participant($open, 'Inès', 'Faure', 'Fonctionnaire', 'Plus de 18 ans', 'Bouvron', null),
            // Long communes for the chart-label truncation ticket (VMM-8).
            // Exactly 25 chars → NOT truncated ; 26 chars → truncated ; long → truncated.
            $this->participant($open, 'Bornage', 'Vingtcinq', 'Autre', 'Plus de 18 ans', 'Blain-Nord-Secteur-Est-12', null),
            $this->participant($open, 'Bornage', 'Vingtsix', 'Autre', 'Plus de 18 ans', 'Blain-Nord-Secteur-Est-123', null),
            $this->participant($open, 'Longue', 'Commune', 'Autre', 'Plus de 18 ans', 'Saint-Étienne-de-Montluc-en-Pays-de-Blain', null),
        ];

        $entreprises = [
            $this->employeur($open, 'Boulangerie du Centre', 'Boulanger·ère', 'Blain'),
            $this->employeur($open, 'Garage Bouvron Auto', 'Mécanicien·ne', 'Bouvron'),
            $this->employeur($open, 'Cabinet Vétérinaire', 'Assistant·e vétérinaire', 'Le Gâvre'),
        ];

        // Quotas d'immersion (SUI-3/SUI-4 « sous quota ») — les immersions ANNULÉES sont
        // exclues du quota (leur créneau redevient libre). Résultat attendu en session ouverte :
        //   Boulangerie (2 places) : Léa confirmée + Hugo ANNULÉE → 1 active/2 → SOUS QUOTA
        //     (le comptage brut = 2 = quota masquerait le besoin ; l'annulée doit être exclue).
        //   Garage (1 place) : Manon ANNULÉE → 0 active/1 → SOUS QUOTA (que des annulées).
        //   Cabinet (1 place) : aucune immersion → 0/1 → SOUS QUOTA.
        //   → 3 entreprises sous quota (contre 1 seule sans l'exclusion des annulées).
        $this->callIfExists($entreprises[0], 'setNombreImmersion', 2);
        $this->callIfExists($entreprises[1], 'setNombreImmersion', 1);
        $this->callIfExists($entreprises[2], 'setNombreImmersion', 1);

        // Scores de compatibilité déterministes pour illustrer les 3 couleurs de badge
        // (≥75 vert / ≥50 orange / <50 rouge) sur les immersions appariées :
        //   Léa ↔ Boulangerie = 100 (vert) · Hugo ↔ Boulangerie = 66 (orange) · Manon ↔ Garage = 33 (rouge).
        $entreprises[0]->setReponsesScore(['question_0' => 4]);
        $entreprises[1]->setReponsesScore(['question_0' => 4]);
        $participants[0]->setReponsesScore(['question_0' => 4]);
        $participants[1]->setReponsesScore(['question_0' => 3]);
        $participants[2]->setReponsesScore(['question_0' => 2]);

        // Matchings — entreprise[0] gets two participants so the partner-recalc
        // path (VMM-3) can be observed when one of them is deactivated.
        // Statuts variés (SUI-4 badges/filtre) — seedés via le helper (enum branche suivi).
        // Dates distinctes pour tester le tri par date (Hugo 10/06 < Manon 15/06 < Léa 20/06).
        $this->match($participants[0], $entreprises[0], 'proposee', '2025-06-20');
        $this->match($participants[1], $entreprises[0], 'annulee', '2025-06-10');
        $this->match($participants[2], $entreprises[1], 'annulee', '2025-06-15');

        // --- Jeu de charge optionnel (~N immersions) pour tester le rendu/perf du suivi ---
        $bulk = max(0, (int) $input->getOption('bulk'));
        if ($bulk > 0) {
            $this->loadBulk($open, $referent, $bulk);
        }

        // --- Closed session with a LATER date than the open one (VMM-1 priority) ---
        $closedRecent = $this->createSession('Session Fermée Déc 2025', '2025-12-01', '2025-12-15', Session::STATUT_FERME, $model);
        $this->participant($closedRecent, 'Camille', 'Moreau', 'Salarié·e', 'Plus de 18 ans', 'Blain', null);
        $this->participant($closedRecent, 'Louis', 'Simon', "Demandeur·euse d'emploi", 'Plus de 18 ans', 'Bouvron', null);
        $this->employeur($closedRecent, 'Fleuriste Le Gâvre', 'Fleuriste', 'Le Gâvre');

        // --- Older closed session (VMM-1 date ordering) ---
        $closedOld = $this->createSession('Session Fermée 2024', '2024-03-01', '2024-03-31', Session::STATUT_FERME, $model);
        $this->participant($closedOld, 'Alice', 'Fontaine', 'Autre', 'Plus de 18 ans', 'La Chevallerais', null);

        // --- Draft session with the LATEST dates of all (VMM-1 brouillon exclusion) ---
        $draft = $this->createSession('Session Brouillon 2026', '2026-06-01', '2026-06-30', Session::STATUT_BROUILLON, $model);
        $this->participant($draft, 'Tom', 'Girard', 'Scolaire, Etudiant·e', 'Moins de 18 ans', 'Blain', null);

        $this->em->flush();

        $io->success('Fixtures loaded.');
        $io->listing([
            'Model: "Questionnaire Test" (communes: Blain, Bouvron, La Chevallerais, Le Gâvre, Autre)',
            'Session Ouverte (ouverte, 2025-06) — 6 participants, 3 entreprises, 3 matchings (entreprise[0] a 2 matchings)',
            'Session Fermée Déc 2025 (fermée, dates postérieures à l\'ouverte)',
            'Session Fermée 2024 (fermée, dates plus anciennes)',
            'Session Brouillon 2026 (brouillon, dates les plus récentes de toutes)',
        ]);

        return Command::SUCCESS;
    }

    private function purge(SymfonyStyle $io): void
    {
        // FK-safe order. Users are intentionally preserved.
        foreach ([Appariement::class, Questionnaire::class, Contact::class, Session::class, ModelQuestionnaire::class, Referent::class] as $class) {
            $this->em->createQuery(sprintf('DELETE FROM %s', $class))->execute();
        }
        $io->note('Existing questionnaire data purged (users kept).');
    }

    private function createModel(): ModelQuestionnaire
    {
        $model = new ModelQuestionnaire();
        $model->setNom('Questionnaire Test');
        $model->setActive(true);
        $model->setCommunes(['Blain', 'Bouvron', 'La Chevallerais', 'Le Gâvre', 'Autre']);
        $model->setQuestionsScore([
            'titre' => 'Questions score',
            'questions' => [
                [
                    'label_participant' => 'Souhaitez-vous communiquer, parler ou expliquer dans votre futur métier ?',
                    'label_employeur' => 'Communication',
                    'reponses_possibles' => [
                        ['valeur' => 1, 'libelle_participant' => 'Pas du tout'],
                        ['valeur' => 2, 'libelle_participant' => 'Un peu'],
                        ['valeur' => 3, 'libelle_participant' => 'Moyennement'],
                        ['valeur' => 4, 'libelle_participant' => 'Beaucoup'],
                    ],
                ],
            ],
        ]);
        $model->setQuestionsDisposition(['titre' => 'Disposition', 'questions' => []]);
        $this->em->persist($model);

        return $model;
    }

    private function createReferent(): Referent
    {
        $referent = new Referent();
        $referent->setPrenom('Alice');
        $referent->setNomFamille('Dubois');
        $referent->setEmail('alice.dubois@mission-locale.fr');
        $referent->setTelephone('0240123456');
        $referent->setFonction('Conseillère');
        $referent->setStructure('Mission Locale');

        $referentUser = $this->em->getRepository(User::class)->findOneBy(['email' => 'referent@vis-mon-metier.fr']);
        if ($referentUser instanceof User && \in_array(Roles::REFERENT, $referentUser->getRoles(), true)) {
            $referent->setUtilisateur($referentUser);
        }

        $this->em->persist($referent);

        return $referent;
    }

    private function createSession(string $nom, string $debut, string $fin, string $statut, ModelQuestionnaire $model): Session
    {
        $session = new Session();
        $session->setNom($nom);
        $session->setDateDebut(new \DateTime($debut));
        $session->setDateFin(new \DateTime($fin));
        $session->setStatut($statut);
        $session->setModelQuestionnaire($model);
        $this->em->persist($session);

        return $session;
    }

    private function participant(Session $session, string $prenom, string $nom, string $situation, string $tranche, string $commune, ?Referent $referent): Questionnaire
    {
        $contact = new Contact();
        $contact->setType(Type::PARTICIPANT);
        $contact->setPrenom($prenom);
        $contact->setNomFamille($nom);
        $contact->setEmail(mb_strtolower($prenom.'.'.$nom).'@example.test');
        $contact->setNumeroTelephone('0600000000');
        $contact->setCommune($commune);
        $contact->setSituation($situation);
        $contact->setTrancheAge($tranche);
        if (null !== $referent) {
            $contact->setReferent($referent);
        }
        $this->em->persist($contact);

        $q = new Questionnaire();
        $q->setSession($session);
        $q->setType(Type::PARTICIPANT);
        $q->setContact($contact);
        $q->setMetier(null);
        $q->setReponsesScore(['question_0' => random_int(1, 4)]);
        $q->setReponsesDisposition([]);
        $this->em->persist($q);

        return $q;
    }

    private function employeur(Session $session, string $entreprise, string $metier, string $commune): Questionnaire
    {
        $contact = new Contact();
        $contact->setType(Type::EMPLOYEUR);
        $contact->setPrenom('Contact');
        $contact->setNomFamille($entreprise);
        $contact->setEmail(mb_strtolower(str_replace(' ', '.', $entreprise)).'@example.test');
        $contact->setNumeroTelephone('0611111111');
        $contact->setCommune($commune);
        $contact->setNomEntreprise($entreprise);
        $this->em->persist($contact);

        $q = new Questionnaire();
        $q->setSession($session);
        $q->setType(Type::EMPLOYEUR);
        $q->setContact($contact);
        $q->setMetier($metier);
        $q->setReponsesScore(['question_0' => random_int(1, 4)]);
        $q->setReponsesDisposition([]);
        $this->em->persist($q);

        return $q;
    }

    /**
     * Génère ~$count immersions supplémentaires dans la session (test de charge),
     * réparties sur quelques entreprises, avec statuts / dates / scores variés.
     */
    private function loadBulk(Session $session, Referent $referent, int $count): void
    {
        $entreprises = [];
        $nbEnt = max(1, (int) ceil($count / 12));
        for ($i = 1; $i <= $nbEnt; ++$i) {
            $entreprises[] = $this->employeur($session, sprintf('Entreprise Charge %02d', $i), 'Métier '.$i, 'Blain');
        }

        $statuts = ['proposee', 'confirmee', 'realisee', 'annulee'];
        $communes = ['Blain', 'Bouvron', 'La Chevallerais', 'Le Gâvre', 'Autre'];
        for ($i = 1; $i <= $count; ++$i) {
            $participant = $this->participant(
                $session,
                'Test'.$i,
                sprintf('Charge%03d', $i),
                'Salarié·e',
                'Plus de 18 ans',
                $communes[$i % 5],
                0 === $i % 3 ? $referent : null,
            );
            $this->match($participant, $entreprises[$i % \count($entreprises)], $statuts[$i % 4], sprintf('2025-06-%02d', ($i % 28) + 1));
        }
    }

    private function match(Questionnaire $participant, Questionnaire $entreprise, ?string $statut = null, string $date = '2025-06-15'): void
    {
        if (!class_exists(Appariement::class)) {
            return;
        }

        $appariement = new Appariement();
        $appariement->setParticipant($participant);
        $appariement->setEntreprise($entreprise);
        $appariement->setDateMatch(new \DateTimeImmutable($date));

        // Statut d'immersion : enum présent uniquement sur la branche suivi-immersions.
        // setStatut appelé via le helper dynamique pour rester portable (et neutre PHPStan).
        if (null !== $statut && enum_exists(StatutImmersion::class)) {
            $case = StatutImmersion::tryFrom($statut);
            if (null !== $case) {
                $this->callIfExists($appariement, 'setStatut', $case);
            }
        }

        $this->em->persist($appariement);

        // `appaire` setter is not present on every branch.
        $this->callIfExists($participant, 'setAppaire', true);
        $this->callIfExists($entreprise, 'setAppaire', true);
    }

    /**
     * Call a setter only when it exists on the target entity, so this fixtures
     * command remains runnable on branches where the field is absent. The dynamic
     * method name keeps it clean under static analysis on every branch.
     */
    private function callIfExists(object $entity, string $method, mixed ...$args): void
    {
        if (method_exists($entity, $method)) {
            $entity->{$method}(...$args);
        }
    }
}
