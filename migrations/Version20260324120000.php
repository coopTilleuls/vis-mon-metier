<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split reponses_possibles[].libelle into libelle_participant and libelle_employeur in questions_disposition and questions_score';
    }

    public function up(Schema $schema): void
    {
        $records = $this->connection->fetchAllAssociative(
            'SELECT id, questions_disposition, questions_score FROM model_questionnaire'
        );

        foreach ($records as $record) {
            $disposition = json_decode($record['questions_disposition'], true);
            $score = json_decode($record['questions_score'], true);

            $dispositionChanged = $this->migrateQuestions($disposition);
            $scoreChanged = $this->migrateQuestions($score);

            if ($dispositionChanged || $scoreChanged) {
                $this->connection->executeStatement(
                    'UPDATE model_questionnaire SET questions_disposition = ?, questions_score = ? WHERE id = ?',
                    [
                        json_encode($disposition, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
                        json_encode($score, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
                        $record['id'],
                    ]
                );
            }
        }
    }

    /**
     * Transforms reponses_possibles[].libelle → libelle_participant + libelle_employeur in-place.
     * Returns true if any change was made.
     */
    private function migrateQuestions(?array &$data): bool
    {
        if (!isset($data['questions'])) {
            return false;
        }

        $changed = false;
        foreach ($data['questions'] as &$question) {
            if (!isset($question['reponses_possibles'])) {
                continue;
            }

            $needsUpgrade = isset($question['reponses_possibles'][0]['libelle'])
                && !isset($question['reponses_possibles'][0]['libelle_participant']);

            if (!$needsUpgrade) {
                continue;
            }

            foreach ($question['reponses_possibles'] as &$reponse) {
                $reponse['libelle_participant'] = $reponse['libelle'];
                $reponse['libelle_employeur'] = $reponse['libelle'];
                unset($reponse['libelle']);
            }
            unset($reponse);
            $changed = true;
        }
        unset($question);

        return $changed;
    }
}
