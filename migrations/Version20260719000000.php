<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260719000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add immersion tracking fields: appariement.statut and contact.observations_volontaire';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE appariement ADD statut VARCHAR(20) DEFAULT 'proposee' NOT NULL");
        $this->addSql('ALTER TABLE contact ADD observations_volontaire TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appariement DROP statut');
        $this->addSql('ALTER TABLE contact DROP observations_volontaire');
    }
}
