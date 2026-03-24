<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add nombreImmersion (int, default 1) and immersionMemeDate (bool, nullable) on questionnaire entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE questionnaire ADD nombre_immersion INT NOT NULL DEFAULT 1');
        $this->addSql('ALTER TABLE questionnaire ADD immersion_meme_date BOOLEAN DEFAULT NULL');
    }
}
