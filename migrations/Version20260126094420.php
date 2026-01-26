<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260126094420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appariement DROP CONSTRAINT fk_ceeeacc8a4aeafea');
        $this->addSql('ALTER TABLE appariement DROP CONSTRAINT fk_ceeeacc89d1c3019');
        $this->addSql('ALTER TABLE appariement ALTER date_match DROP NOT NULL');
        $this->addSql('ALTER TABLE appariement ALTER participant_id DROP NOT NULL');
        $this->addSql('ALTER TABLE appariement ALTER entreprise_id DROP NOT NULL');
        $this->addSql('ALTER TABLE appariement ADD CONSTRAINT FK_CEEEACC8A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE appariement ADD CONSTRAINT FK_CEEEACC89D1C3019 FOREIGN KEY (participant_id) REFERENCES questionnaire (id) ON DELETE CASCADE NOT DEFERRABLE');
    }
}
