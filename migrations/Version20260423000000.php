<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260423000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add RGPD consent fields (consentement_rgpd, consentement_rgpd_at) to contact table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD consentement_rgpd BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD consentement_rgpd_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN contact.consentement_rgpd_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
