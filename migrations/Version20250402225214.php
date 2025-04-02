<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402225214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD description VARCHAR(75) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer RENAME INDEX fk_f67d02e934ecb4e6 TO fk_id_route_offer
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer RENAME INDEX fk_f67d02e9c3c6f69f TO fk_id_car_offer
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_284ab62934ecb4e6 TO fk_id_route_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_284ab62953c674ee TO fk_id_offer_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_284ab629a76ed395 TO fk_id_user_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car RENAME INDEX fk_a54c9923751c934 TO fk_id_driver_private_car
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer RENAME INDEX fk_id_car_offer TO FK_F67D02E9C3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer RENAME INDEX fk_id_route_offer TO FK_F67D02E934ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_id_offer_reservation TO FK_284AB62953C674EE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_id_route_reservation TO FK_284AB62934ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation RENAME INDEX fk_id_user_reservation TO FK_284AB629A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car RENAME INDEX fk_id_driver_private_car TO FK_A54C9923751C934
        SQL);
    }
}
