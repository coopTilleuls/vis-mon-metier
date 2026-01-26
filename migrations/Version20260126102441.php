<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260126102441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE questionnaire DROP CONSTRAINT fk_7a64dafe7a1254a');
        $this->addSql('ALTER TABLE questionnaire ADD CONSTRAINT FK_7A64DAFE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE referent DROP CONSTRAINT fk_fe9aac6cfb88e14f');
        $this->addSql('ALTER TABLE referent ADD CONSTRAINT FK_FE9AAC6CFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE session DROP CONSTRAINT fk_d044d5d456fae74d');
        $this->addSql('ALTER TABLE session ADD CONSTRAINT FK_D044D5D456FAE74D FOREIGN KEY (model_questionnaire_id) REFERENCES model_questionnaire (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE session ALTER model_questionnaire_id DROP NOT NULL');
    }
}
