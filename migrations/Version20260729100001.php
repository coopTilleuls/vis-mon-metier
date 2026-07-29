<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729100001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add actif flag to questionnaire table (used to deactivate volontaires from the matching pool)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE questionnaire ADD actif BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE questionnaire DROP actif');
    }
}
