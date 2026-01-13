<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260113094737 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP nom_referent');
        $this->addSql('ALTER TABLE contact DROP prenom_referent');
        $this->addSql('ALTER TABLE contact DROP structure_referent');
    }
}
