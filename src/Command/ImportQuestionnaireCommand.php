<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Contact;
use App\Entity\ModelQuestionnaire;
use App\Entity\Questionnaire;
use App\Entity\Referent;
use App\Entity\Roles;
use App\Entity\Session;
use App\Entity\User;
use App\ValueObject\Type;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:import-data-2024',
    description: 'Imports questionnaires from answers_2024.csv and sets up environment (Users, Model, Session).',
)]
class ImportQuestionnaireCommand extends Command
{
    /**
     * @var array<string, mixed>
     */
    private array $questionsDispositionSchema;

    /**
     * @throws \JsonException
     * @throws \RuntimeException
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KernelInterface $kernel,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
        $schemaPath = $this->kernel->getProjectDir().'/src/DataFixtures/questionsDisposition.json';

        $content = file_get_contents($schemaPath);
        if (false === $content) {
            throw new \RuntimeException(sprintf('Unable to read schema file at "%s"', $schemaPath));
        }

        $this->questionsDispositionSchema = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Starting 2024 Data Import...');

        $this->ensureUsers($io);

        $referent = $this->ensureReferent($io);

        $model = $this->ensureModelQuestionnaire($io);
        if (!$model) {
            return Command::FAILURE;
        }

        $session = $this->ensureSession($io, $model);

        return $this->importCsv($io, $session, $referent);
    }

    private function ensureUsers(SymfonyStyle $io): void
    {
        $usersConfig = [
            [
                'email' => 'referent.example@vis-mon-metier.fr',
                'role' => Roles::REFERENT,
            ],
        ];

        $createdCount = 0;

        foreach ($usersConfig as $userData) {
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);

            if (!$existingUser) {
                $user = new User();
                $user->setEmail($userData['email']);
                $user->setRoles([$userData['role']]);

                $hashedPassword = $this->passwordHasher->hashPassword($user, '123456');
                $user->setPassword($hashedPassword);

                $this->entityManager->persist($user);
                ++$createdCount;
            }
        }

        $this->entityManager->flush();

        if ($createdCount > 0) {
            $io->success(sprintf('%d users created.', $createdCount));
        }
    }

    private function ensureReferent(SymfonyStyle $io): Referent
    {
        $repo = $this->entityManager->getRepository(Referent::class);
        $referentEmail = 'alice.dubois@mission-locale.fr';
        $referent = $repo->findOneBy(['email' => $referentEmail]);

        if ($referent) {
            $io->note('Using existing referent: Alice Dubois');

            return $referent;
        }

        $io->section('Creating Referent');
        $referentUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'referent.example@vis-mon-metier.fr']);

        if (!$referentUser) {
            // Should be created by ensureUsers, but just in case
            $io->warning('Referent user not found, creating a new one specifically for linking.');
            $referentUser = new User();
            $referentUser->setEmail('referent.example@vis-mon-metier.fr');
            $referentUser->setRoles([Roles::REFERENT]);
            $referentUser->setPassword($this->passwordHasher->hashPassword($referentUser, '123456'));
            $this->entityManager->persist($referentUser);
        }

        $referent = new Referent();
        $referent->setPrenom('Alice');
        $referent->setNomFamille('Dubois');
        $referent->setEmail($referentEmail);
        $referent->setTelephone('0240123456');
        $referent->setFonction('Conseillère');
        $referent->setStructure('Mission Locale');
        $referent->setUtilisateur($referentUser);

        $this->entityManager->persist($referent);
        $this->entityManager->flush();
        $io->success('Referent created and linked to user.');

        return $referent;
    }

    private function ensureModelQuestionnaire(SymfonyStyle $io): ?ModelQuestionnaire
    {
        $repo = $this->entityManager->getRepository(ModelQuestionnaire::class);

        $model = $repo->findOneBy(['nom' => 'Questionnaire VmM 2024']);

        if ($model) {
            return $model;
        }

        $io->section('Creating ModelQuestionnaire');
        $projectDir = $this->kernel->getProjectDir();
        $scorePath = $projectDir.'/src/DataFixtures/questionsScore.json';
        $dispPath = $projectDir.'/src/DataFixtures/questionsDisposition.json';

        if (!file_exists($scorePath) || !file_exists($dispPath)) {
            $io->error('Missing JSON configuration files in src/DataFixtures/');

            return null;
        }

        try {
            $scoreContent = file_get_contents($scorePath);
            $dispContent = file_get_contents($dispPath);

            if (false === $scoreContent || false === $dispContent) {
                throw new \RuntimeException('Failed to read JSON files');
            }

            $scoreData = json_decode($scoreContent, true, 512, JSON_THROW_ON_ERROR);
            $dispData = json_decode($dispContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $io->error('Error decoding JSON files: '.$e->getMessage());

            return null;
        }

        $model = new ModelQuestionnaire();
        $model->setNom('Questionnaire VmM 2024');
        $model->setActive(true);
        $model->setQuestionsScore($scoreData);
        $model->setQuestionsDisposition($dispData);

        // Setup default communes as simple strings
        $model->setCommunes([
            'Blain',
            'Bouvron',
            'La Chevallerais',
            'Le Gâvre',
            'Autre',
        ]);

        $this->entityManager->persist($model);
        $this->entityManager->flush();
        $io->success('ModelQuestionnaire created.');

        return $model;
    }

    private function ensureSession(SymfonyStyle $io, ModelQuestionnaire $model): Session
    {
        $repo = $this->entityManager->getRepository(Session::class);
        // Try to find the specific session for 2024
        $session = $repo->findOneBy(['nom' => 'Session Import 2024']);

        if ($session) {
            $io->note('Using existing session: '.$session->getNom());

            return $session;
        }

        $io->section('Creating Session');
        $session = new Session();
        $session->setNom('Session Import 2024');
        $session->setDateDebut(new \DateTime('2024-01-01'));
        $session->setDateFin(new \DateTime('2024-12-31'));
        $session->setStatut(Session::STATUT_OUVERT);
        $session->setModelQuestionnaire($model);

        $this->entityManager->persist($session);
        $this->entityManager->flush();
        $io->success('Session created.');

        return $session;
    }

    private function importCsv(SymfonyStyle $io, Session $session, Referent $referent): int
    {
        $filePath = $this->kernel->getProjectDir().'/src/DataFixtures/answers_2024.csv';

        if (!file_exists($filePath)) {
            $io->error('File not found: '.$filePath);

            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'rb');
        if (false === $handle) {
            $io->error('Unable to open file: '.$filePath);

            return Command::FAILURE;
        }

        $io->title('Importing Questionnaires from CSV...');
        $count = 0;
        $linkedParticipantsCount = 0;

        $model = $session->getModelQuestionnaire();
        $communesList = $model?->getCommunes();

        if (empty($communesList)) {
            $io->warning('No communes found for this model. Contacts will not have communes assigned.');
        }

        try {
            // Skip the header row
            fgetcsv($handle, 0, ';');

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                $type = $row[1];

                $questionnaire = new Questionnaire();
                $questionnaire->setSession($session);
                $questionnaire->setType($type);
                $questionnaire->setMetier(!empty($row[2]) ? $row[2] : null);

                $reponsesScore = [];
                for ($i = 0; $i < 18; ++$i) {
                    $csvIndex = 3 + $i;
                    $val = isset($row[$csvIndex]) ? (int) $row[$csvIndex] : null;
                    if ($val > 0) {
                        $reponsesScore['question_'.$i] = $val;
                    }
                }
                $questionnaire->setReponsesScore($reponsesScore);

                $dispositionMapping = [0 => 22, 1 => 23, 2 => 24, 3 => 25, 6 => 27];
                $reponsesDisposition = [];

                foreach ($dispositionMapping as $qIndex => $csvIndex) {
                    if (!empty($row[$csvIndex])) {
                        if (6 === $qIndex) {
                            $reponsesDisposition['question_'.$qIndex] = $row[$csvIndex];
                        } else {
                            $valId = $this->findOptionId($qIndex, $row[$csvIndex]);
                            if (null !== $valId) {
                                $reponsesDisposition['question_'.$qIndex] = $valId;
                            }
                        }
                    }
                }

                if (!empty($row[31])) {
                    $parsedDates = $this->parseDates($row[31]);
                    $dates = array_map(fn (\DateTimeImmutable $date) => $date->format('Y-m-d'), $parsedDates);

                    if (!empty($dates)) {
                        $dateQuestionIndex = $this->findDateQuestionIndex();
                        if (null !== $dateQuestionIndex) {
                            $reponsesDisposition['question_'.$dateQuestionIndex] = $dates;
                        }
                    }
                }

                $questionnaire->setReponsesDisposition($reponsesDisposition);

                $contact = new Contact();
                $contact->setType($row[1]);
                $contact->setPrenom($row[36]);
                $contact->setNomFamille($row[37]);
                $contact->setEmail($row[38]);
                $contact->setNumeroTelephone($row[39]);

                if (!empty($communesList)) {
                    $randomCommune = $communesList[array_rand($communesList)];
                    $contact->setCommune($randomCommune);
                }

                if ('employeur' === $type) {
                    $contact->setNomEntreprise($row[33]);
                } else {
                    $contact->setTrancheAge($row[34]);
                    $contact->setSituation($row[35]);

                    // Link the first 15 participants to the referent
                    if (Type::PARTICIPANT === $type && $linkedParticipantsCount < 15) {
                        $contact->setReferent($referent);
                        ++$linkedParticipantsCount;
                    }
                }

                $this->entityManager->persist($contact);
                $questionnaire->setContact($contact);

                $this->entityManager->persist($questionnaire);
                ++$count;

                if (0 === $count % 20) {
                    $this->entityManager->flush();
                }
            }

            $this->entityManager->flush();
        } finally {
            fclose($handle);
        }

        $io->success(sprintf('Imported %d questionnaires successfully.', $count));
        $io->success(sprintf('Linked %d participants to referent %s.', $linkedParticipantsCount, $referent->getPrenom().' '.$referent->getNomFamille()));

        return Command::SUCCESS;
    }

    private function findDateQuestionIndex(): ?int
    {
        if (!isset($this->questionsDispositionSchema['questions'])) {
            return null;
        }

        foreach ($this->questionsDispositionSchema['questions'] as $index => $question) {
            if (isset($question['type_reponse']) && 'choix dates multiples' === $question['type_reponse']) {
                return $index;
            }
        }

        foreach ($this->questionsDispositionSchema['questions'] as $index => $question) {
            if (isset($question['label_employeur']) && str_contains($question['label_employeur'], 'Jours de disponibilité')) {
                return $index;
            }
        }

        return null;
    }

    private function findOptionId(int $questionIndex, string $csvValue): ?int
    {
        if (empty($csvValue)) {
            return null;
        }

        if (!isset($this->questionsDispositionSchema['questions'][$questionIndex])) {
            return null;
        }

        $question = $this->questionsDispositionSchema['questions'][$questionIndex];
        $cleanCsv = mb_strtolower(trim($csvValue));

        foreach ($question['reponses_possibles'] as $option) {
            $libelle = mb_strtolower(trim($option['libelle']));

            if ($cleanCsv === $libelle) {
                return $option['valeur'];
            }

            if ('' !== $libelle && str_starts_with($cleanCsv, $libelle)) {
                return $option['valeur'];
            }

            if ('' !== $cleanCsv && str_starts_with($libelle, $cleanCsv)) {
                return $option['valeur'];
            }
        }

        return null;
    }

    /**
     * @return array<int, \DateTimeImmutable>
     */
    private function parseDates(string $dateString): array
    {
        $dates = [];
        $parts = preg_split('/[;\n\r]+/', $dateString);

        if (false === $parts) {
            return [];
        }

        $months = [
            'janvier' => '01', 'fevrier' => '02', 'février' => '02',
            'mars' => '03', 'avril' => '04', 'mai' => '05',
            'juin' => '06', 'juillet' => '07', 'aout' => '08', 'août' => '08',
            'septembre' => '09', 'octobre' => '10', 'novembre' => '11', 'decembre' => '12', 'décembre' => '12',
        ];

        foreach ($parts as $part) {
            $part = trim($part);
            if (empty($part)) {
                continue;
            }

            if (preg_match('/(\d{1,2})\s+([a-zA-Zûé]+)/u', $part, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $monthStr = strtolower($matches[2]);

                if (isset($months[$monthStr])) {
                    $month = $months[$monthStr];
                    try {
                        $dates[] = new \DateTimeImmutable("2024-$month-$day");
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return $dates;
    }
}
