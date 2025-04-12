<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409103830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity CHANGE dateDebut dateDebut DATETIME NOT NULL, CHANGE dateFin dateFin DATETIME NOT NULL, CHANGE description description VARCHAR(30) NOT NULL, CHANGE localisation localisation VARCHAR(30) NOT NULL, CHANGE typeActivity typeActivity VARCHAR(50) NOT NULL, CHANGE joinHotelId joinHotelId INT DEFAULT NULL, CHANGE joinVoitureId joinVoitureId INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095A68F4013D FOREIGN KEY (joinHotelId) REFERENCES hotel (id_hotel_h)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095AD6CB0699 FOREIGN KEY (joinVoitureId) REFERENCES private_car (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095A3F2EFA5B FOREIGN KEY (joinVolsId) REFERENCES flights (id_flight)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity ADD CONSTRAINT FK_AC74095AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE airlines MODIFY airline_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON airlines
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE airlines ADD PRIMARY KEY (airline_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY fk_id_car_offer
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY fk_id_route_offer
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT FK_F67D02E9C3C6F69F FOREIGN KEY (car_id) REFERENCES private_car (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT FK_F67D02E934ECB4E6 FOREIGN KEY (route_id) REFERENCES car_route (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY fk_id_offer_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY fk_id_route_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY fk_id_user_reservation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation CHANGE route_id route_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT FK_284AB62953C674EE FOREIGN KEY (offer_id) REFERENCES car_offer (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT FK_284AB62934ECB4E6 FOREIGN KEY (route_id) REFERENCES car_route (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT FK_284AB629A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights DROP FOREIGN KEY flights_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights CHANGE airline_name airline_name VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights ADD CONSTRAINT FK_FC74B5EA22608261 FOREIGN KEY (airline_name) REFERENCES airlines (airline_name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car DROP FOREIGN KEY fk_id_driver_private_car
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car ADD CONSTRAINT FK_A54C9923751C934 FOREIGN KEY (id_driver) REFERENCES car_driver (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel DROP FOREIGN KEY reservation_hotel_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel CHANGE id_chambre_j id_chambre_j INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel ADD CONSTRAINT FK_402C8E7E65EB791F FOREIGN KEY (id_chambre_j) REFERENCES chambre (id_chambre_h)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review CHANGE commentaire commentaire TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY tickets_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets CHANGE id_flight id_flight INT DEFAULT NULL, CHANGE passenger_id passenger_id INT DEFAULT NULL, CHANGE selected_user selected_user INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4E46053E3 FOREIGN KEY (id_flight) REFERENCES flights (id_flight)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeactivity CHANGE nomEvenement nomEvenement VARCHAR(30) NOT NULL, CHANGE nomType nomType VARCHAR(30) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A68F4013D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AD6CB0699
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A3F2EFA5B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity DROP FOREIGN KEY FK_AC74095AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE activity CHANGE dateDebut dateDebut DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE dateFin dateFin DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL, CHANGE description description VARCHAR(30) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE localisation localisation VARCHAR(30) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE typeActivity typeActivity VARCHAR(255) NOT NULL, CHANGE joinHotelId joinHotelId INT NOT NULL, CHANGE joinVoitureId joinVoitureId INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE airlines MODIFY airline_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON airlines
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE airlines ADD PRIMARY KEY (airline_id, airline_name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY FK_F67D02E9C3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY FK_F67D02E934ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT fk_id_car_offer FOREIGN KEY (car_id) REFERENCES private_car (id) ON UPDATE SET NULL ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT fk_id_route_offer FOREIGN KEY (route_id) REFERENCES car_route (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY FK_284AB62953C674EE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY FK_284AB62934ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation DROP FOREIGN KEY FK_284AB629A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation CHANGE route_id route_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT fk_id_offer_reservation FOREIGN KEY (offer_id) REFERENCES car_offer (id) ON UPDATE SET NULL ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT fk_id_route_reservation FOREIGN KEY (route_id) REFERENCES car_route (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_reservation ADD CONSTRAINT fk_id_user_reservation FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights DROP FOREIGN KEY FK_FC74B5EA22608261
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights CHANGE airline_name airline_name VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flights ADD CONSTRAINT flights_ibfk_1 FOREIGN KEY (airline_name) REFERENCES airlines (airline_name) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car DROP FOREIGN KEY FK_A54C9923751C934
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car ADD CONSTRAINT fk_id_driver_private_car FOREIGN KEY (id_driver) REFERENCES car_driver (id) ON UPDATE SET NULL ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel DROP FOREIGN KEY FK_402C8E7E65EB791F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel CHANGE id_chambre_j id_chambre_j INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel ADD CONSTRAINT reservation_hotel_ibfk_1 FOREIGN KEY (id_chambre_j) REFERENCES chambre (id_chambre_h) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE review CHANGE commentaire commentaire TEXT CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4E46053E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets CHANGE id_flight id_flight INT NOT NULL, CHANGE passenger_id passenger_id INT NOT NULL, CHANGE selected_user selected_user INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT tickets_ibfk_1 FOREIGN KEY (id_flight) REFERENCES flights (id_flight) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE typeactivity CHANGE nomEvenement nomEvenement VARCHAR(30) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, CHANGE nomType nomType VARCHAR(30) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`
        SQL);
    }
}
