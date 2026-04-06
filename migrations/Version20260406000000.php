<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260406000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Split introduction into introduction_participant and introduction_employeur in questions_disposition and questions_score';
    }

    public function up(Schema $schema): void
    {
        $records = $this->connection->fetchAllAssociative(
            'SELECT id, questions_disposition, questions_score FROM model_questionnaire'
        );

        foreach ($records as $record) {
            $disposition = json_decode($record['questions_disposition'], true);
            $score = json_decode($record['questions_score'], true);

            $dispositionChanged = $this->migrateIntroduction($disposition);
            $scoreChanged = $this->migrateIntroduction($score);

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

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Transforms introduction → introduction_participant + introduction_employeur in-place.
     * Returns true if any change was made.
     */
    private function migrateIntroduction(?array &$data): bool
    {
        if (!isset($data['introduction']) || isset($data['introduction_participant'])) {
            return false;
        }

        $data['introduction_participant'] = $data['introduction'];
        $data['introduction_employeur'] = $data['introduction'];
        unset($data['introduction']);

        return true;
    }
}
