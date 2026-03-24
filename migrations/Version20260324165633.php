<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324165633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add signatory/tutor/address fields and migrate employee count to predefined ranges';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD fonction_signataire VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD fonction_tuteur VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD email_tuteur VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD telephone_tuteur VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact ADD adresse_entreprise VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE model_questionnaire ALTER communes DROP DEFAULT');

        // Migrate existing numeric values to predefined ranges
        $this->addSql("
            UPDATE contact
            SET nombre_employes = CASE
                WHEN nombre_employes ~ '^\d+$' AND nombre_employes::integer <= 10  THEN '- de 10 salarié·e·s'
                WHEN nombre_employes ~ '^\d+$' AND nombre_employes::integer <= 50  THEN 'Entre 11 et 50 salarié·e·s'
                WHEN nombre_employes ~ '^\d+$' AND nombre_employes::integer <= 250 THEN 'Entre 51 et 250 salarié·e·s'
                WHEN nombre_employes ~ '^\d+$' AND nombre_employes::integer > 250  THEN '+ de 250 salarié·e·s'
                ELSE nombre_employes
            END
            WHERE nombre_employes IS NOT NULL
              AND nombre_employes NOT IN (
                '- de 10 salarié·e·s',
                'Entre 11 et 50 salarié·e·s',
                'Entre 51 et 250 salarié·e·s',
                '+ de 250 salarié·e·s'
              )
        ");
    }
}
