<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114102142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_ceeeacc8a4aeafea');
        $this->addSql('DROP INDEX uniq_ceeeacc89d1c3019');
        $this->addSql('CREATE INDEX IDX_CEEEACC89D1C3019 ON appariement (participant_id)');
        $this->addSql('CREATE INDEX IDX_CEEEACC8A4AEAFEA ON appariement (entreprise_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CEEEACC89D1C3019');
        $this->addSql('DROP INDEX IDX_CEEEACC8A4AEAFEA');
        $this->addSql('CREATE UNIQUE INDEX uniq_ceeeacc8a4aeafea ON appariement (entreprise_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_ceeeacc89d1c3019 ON appariement (participant_id)');
    }
}
