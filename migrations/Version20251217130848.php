<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251217130848 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent ADD structure VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD prenom_tuteur VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD nom_famille_tuteur VARCHAR(255) DEFAULT NULL');
    }
}
