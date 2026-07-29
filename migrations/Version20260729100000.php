<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260729100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add independent registration flags (inscription_volontaire_ouverte, inscription_entreprise_ouverte) to session table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session ADD inscription_volontaire_ouverte BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE session ADD inscription_entreprise_ouverte BOOLEAN DEFAULT true NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session DROP inscription_volontaire_ouverte');
        $this->addSql('ALTER TABLE session DROP inscription_entreprise_ouverte');
    }
}
