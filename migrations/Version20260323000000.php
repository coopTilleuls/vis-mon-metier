<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260323000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add administrative fields for immersion setup on contact (siret, horaires, epi_disponible, autres_mesures_prevention, precision_mesures_prevention) and drop unused commune table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD siret VARCHAR(14) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD horaires VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD epi_disponible BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD autres_mesures_prevention BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD precision_mesures_prevention TEXT DEFAULT NULL');
        $this->addSql('DROP TABLE commune');
    }
}
