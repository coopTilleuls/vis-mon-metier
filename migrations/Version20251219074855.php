<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251219074855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD nom_referent VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD prenom_referent VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD structure_referent VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact DROP CONSTRAINT fk_4c62e63835e47e35');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E63835E47E35 FOREIGN KEY (referent_id) REFERENCES referent (id) ON DELETE SET NULL NOT DEFERRABLE');
    }
}
