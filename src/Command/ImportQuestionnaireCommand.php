<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Contact;
use App\Entity\Questionnaire;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:import-data-2024',
    description: 'Imports questionnaires from answers_2024.csv',
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
        $filePath = $this->kernel->getProjectDir().'/src/DataFixtures/answers_2024.csv';

        if (!file_exists($filePath)) {
            $io->error('File not found: '.$filePath);

            return Command::FAILURE;
        }

        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['statut' => Session::STATUT_OUVERT], ['id' => 'DESC']);
        if (!$session) {
            $io->error('No session found.');

            return Command::FAILURE;
        }

        $handle = fopen($filePath, 'rb');
        if (false === $handle) {
            $io->error('Unable to open file: '.$filePath);

            return Command::FAILURE;
        }

        $io->title('Importing Questionnaires...');
        $count = 0;

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
                $contact->setCommune('Blain');
                if ('employeur' === $type) {
                    $contact->setNomEntreprise($row[33]);
                } else {
                    $contact->setTrancheAge($row[34]);
                    $contact->setSituation($row[35]);
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
