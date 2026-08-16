<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Questionnaire;
use App\Entity\Session;
use App\ValueObject\StatutImmersion;
use App\ValueObject\Type;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Questionnaire>
 */
class QuestionnaireRepository extends ServiceEntityRepository
{
    use ReferentFilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Questionnaire::class);
    }

    /**
     * @return list<Questionnaire>
     */
    public function findOpposingCandidatesFor(Questionnaire $questionnaire): array
    {
        $targetType = Type::EMPLOYEUR === $questionnaire->getType() ? Type::PARTICIPANT : Type::EMPLOYEUR;

        /** @var list<Questionnaire> $result */
        $result = $this->createQueryBuilder('q')
            ->where('q.session = :session')
            ->andWhere('q.type = :targetType')
            ->andWhere('q.actif = true')
            ->setParameter('session', $questionnaire->getSession())
            ->setParameter('targetType', $targetType)
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * Volontaires d'une session sans aucune immersion (lignes « fantômes » du suivi), avec
     * fetch-join contact + référent. Filtre optionnel par référents.
     *
     * @param list<int>|null $referentIds
     *
     * @return list<Questionnaire>
     */
    public function findParticipantsSansImmersion(Session $session, ?array $referentIds = null): array
    {
        $qb = $this->createQueryBuilder('q')
            ->join('q.contact', 'c')->addSelect('c')
            ->leftJoin('c.referent', 'r')->addSelect('r')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.appaire = false')
            ->orderBy('c.nomFamille', 'ASC')
            ->addOrderBy('c.prenom', 'ASC')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT);

        $this->applyReferentFilter($qb, $referentIds);

        /** @var list<Questionnaire> $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param list<int>|null $referentIds
     */
    public function countParticipantsSansImmersion(Session $session, ?array $referentIds = null): int
    {
        $qb = $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->leftJoin('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.appaire = false')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT);

        $this->applyReferentFilter($qb, $referentIds);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Nombre d'entreprises dont le quota d'immersions n'est pas rempli
     * (immersions non annulées < nombreImmersion). Les immersions au statut
     * « annulée » ne comptent pas : leur créneau est de nouveau disponible.
     */
    public function countEnterprisesUnderQuota(Session $session): int
    {
        $rows = $this->createQueryBuilder('q')
            ->select('q.id')
            ->leftJoin('q.appariementsEntreprise', 'a')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('q.id')
            ->having('sum(case when a.statut != :annulee then 1 else 0 end) < q.nombreImmersion')
            ->setParameter('session', $session)
            ->setParameter('type', Type::EMPLOYEUR)
            ->setParameter('annulee', StatutImmersion::Annulee->value)
            ->getQuery()
            ->getResult();

        return count($rows);
    }

    public function countInactiveParticipants(Session $session): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.actif = false')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countActiveParticipants(Session $session): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.actif = true')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countJobs(Session $session): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', Type::EMPLOYEUR)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsByAge(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.trancheAge as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.trancheAge')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsBySituation(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.situation as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.situation')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsByAccompaniment(Session $session): array
    {
        $accompanied = (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.appaire = :appaire')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->setParameter('appaire', true)
            ->getQuery()
            ->getSingleScalarResult();

        $notAccompanied = (int) $this->createQueryBuilder('q')
            ->select('count(q.id)')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->andWhere('q.appaire = :appaire')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->setParameter('appaire', false)
            ->getQuery()
            ->getSingleScalarResult();

        if (0 === $accompanied && 0 === $notAccompanied) {
            return [];
        }

        return [
            ['label' => 'Accompagnés', 'count' => $accompanied],
            ['label' => 'Non accompagnés', 'count' => $notAccompanied],
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getEnterprisesByCommune(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.commune as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.commune')
            ->orderBy('count', 'DESC')
            ->setParameter('session', $session)
            ->setParameter('type', Type::EMPLOYEUR)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsByCommune(Session $session): array
    {
        return $this->createQueryBuilder('q')
            ->select('c.commune as label, count(q.id) as count')
            ->join('q.contact', 'c')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->groupBy('c.commune')
            ->orderBy('count', 'DESC')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<mixed>
     */
    public function getParticipantsByDateCount(Session $session): array
    {
        $questionnaires = $this->createQueryBuilder('q')
            ->where('q.session = :session')
            ->andWhere('q.type = :type')
            ->setParameter('session', $session)
            ->setParameter('type', Type::PARTICIPANT)
            ->getQuery()
            ->getResult();

        $questionsSchema = $session->getModelQuestionnaire()?->getQuestionsDisposition() ?? [];
        $dateCounts = [];

        foreach ($questionnaires as $questionnaire) {
            $dates = $this->extractDatesFromAnswers($questionnaire->getReponsesDisposition() ?? [], $questionsSchema);
            $count = count($dates);

            if (!isset($dateCounts[$count])) {
                $dateCounts[$count] = 0;
            }
            ++$dateCounts[$count];
        }

        ksort($dateCounts);

        $result = [];
        foreach ($dateCounts as $count => $number) {
            if (0 === $count) {
                $label = 'Non disponible';
            } else {
                $label = 1 === $count ? '1 jour' : $count.' jours';
            }
            $result[] = ['label' => $label, 'count' => $number];
        }

        return $result;
    }

    /**
     * @param array<mixed> $answers
     * @param array<mixed> $schema
     *
     * @return array<string>
     */
    private function extractDatesFromAnswers(array $answers, array $schema): array
    {
        $normalized = [];
        $questions = $schema['questions'] ?? [];
        $targetKey = null;

        foreach ($questions as $index => $question) {
            if (isset($question['type_reponse']) && 'choix dates multiples' === $question['type_reponse']) {
                $targetKey = 'question_'.$index;
                break;
            }
        }

        if (null !== $targetKey && isset($answers[$targetKey]) && is_array($answers[$targetKey])) {
            foreach ($answers[$targetKey] as $dateStr) {
                try {
                    $normalized[] = (new \DateTime($dateStr))->format('d/m');
                } catch (\Exception) {
                }
            }
        }

        return array_unique($normalized);
    }

    /**
     * @return array<mixed>
     */
    public function getStatisticsByQuestion(Session $session): array
    {
        $questionnaires = $this->createQueryBuilder('q')
            ->where('q.session = :session')
            ->setParameter('session', $session)
            ->getQuery()
            ->getResult();

        $questionsScoreSchema = $session->getModelQuestionnaire()?->getQuestionsScore() ?? [];
        $questions = $questionsScoreSchema['questions'] ?? [];

        if (empty($questions)) {
            return [];
        }

        $stats = [];
        foreach ($questions as $index => $questionSchema) {
            $key = 'question_'.$index;
            $label = $questionSchema['label_participant'] ?? "Question $index";

            $countsParticipant = [];
            $countsEmployeur = [];

            foreach ($questionnaires as $questionnaire) {
                $responses = $questionnaire->getReponsesScore() ?? [];
                if (isset($responses[$key])) {
                    $value = (string) $responses[$key];

                    if (Type::PARTICIPANT === $questionnaire->getType()) {
                        if (!isset($countsParticipant[$value])) {
                            $countsParticipant[$value] = 0;
                        }
                        ++$countsParticipant[$value];
                    } else {
                        if (!isset($countsEmployeur[$value])) {
                            $countsEmployeur[$value] = 0;
                        }
                        ++$countsEmployeur[$value];
                    }
                }
            }

            if (!empty($countsParticipant) || !empty($countsEmployeur)) {
                $reponsesMap = [];
                $reponsesOrder = [];
                foreach ($questionSchema['reponses_possibles'] ?? [] as $reponse) {
                    $valeur = (string) $reponse['valeur'];
                    $reponsesMap[$valeur] = $reponse['libelle_participant'] ?? $reponse['libelle'] ?? $valeur;
                    $reponsesOrder[] = $valeur;
                }

                $data = [];

                // Iterate through answers in the order they're defined in the schema
                foreach ($reponsesOrder as $value) {
                    $countP = $countsParticipant[$value] ?? 0;
                    $countE = $countsEmployeur[$value] ?? 0;

                    if ($countP > 0 || $countE > 0) {
                        $data[] = [
                            'label' => $reponsesMap[$value] ?? $value,
                            'countParticipant' => $countP,
                            'countEmployeur' => $countE,
                        ];
                    }
                }

                $stats[] = [
                    'index' => $index,
                    'title' => $label,
                    'key' => $key,
                    'data' => $data,
                ];
            }
        }

        usort($stats, function ($a, $b) {
            return $a['index'] <=> $b['index'];
        });

        return $stats;
    }
}
