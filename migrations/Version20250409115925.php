<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409115925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, dateDebut DATETIME NOT NULL, dateFin DATETIME NOT NULL, description VARCHAR(30) NOT NULL, localisation VARCHAR(30) NOT NULL, prixTotal NUMERIC(30, 0) NOT NULL, nomActivity VARCHAR(100) NOT NULL, typeActivity VARCHAR(255) NOT NULL, joinHotelId INT NOT NULL, joinVoitureId INT NOT NULL, joinVolsId INT NOT NULL, user_id INT DEFAULT NULL, INDEX joinHotelId (joinHotelId), INDEX joinVoitureId (joinVoitureId), INDEX joinVolsId (joinVolsId), INDEX fk_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE airlines (airline_id INT NOT NULL, airline_name VARCHAR(100) NOT NULL, airline_iata_code VARCHAR(15) NOT NULL, airline_country VARCHAR(50) NOT NULL, UNIQUE INDEX airline_name (airline_name), PRIMARY KEY(airline_id, airline_name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE car_driver (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(30) NOT NULL, last_name VARCHAR(30) NOT NULL, phone VARCHAR(15) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE car_offer (id INT AUTO_INCREMENT NOT NULL, car_id INT DEFAULT NULL, route_id INT DEFAULT NULL, description VARCHAR(75) NOT NULL, date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, price DOUBLE PRECISION DEFAULT '0' NOT NULL, INDEX fk_id_route_offer (route_id), INDEX fk_id_car_offer (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE car_reservation (id INT AUTO_INCREMENT NOT NULL, offer_id INT DEFAULT NULL, route_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX fk_id_route_reservation (route_id), INDEX fk_id_offer_reservation (offer_id), INDEX fk_id_user_reservation (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE car_route (id INT AUTO_INCREMENT NOT NULL, date_start DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, location_start VARCHAR(50) NOT NULL, location_destination VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE chambre (id_chambre_h INT AUTO_INCREMENT NOT NULL, type_chambre_h VARCHAR(255) NOT NULL, prix_nuit_h INT NOT NULL, dispo_h DATE NOT NULL, option_h VARCHAR(255) NOT NULL, id_hotel_j INT NOT NULL, INDEX forkei1 (id_hotel_j), PRIMARY KEY(id_chambre_h)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE flight_reservations (id INT AUTO_INCREMENT NOT NULL, booking_date DATE NOT NULL, status VARCHAR(50) NOT NULL, flight_id INT NOT NULL, user_id INT NOT NULL, INDEX user_id (user_id), INDEX flight_id (flight_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE flights (id_flight INT AUTO_INCREMENT NOT NULL, airline_name VARCHAR(100) DEFAULT NULL, flight_number VARCHAR(15) NOT NULL, departure_airport_name VARCHAR(100) NOT NULL, arrival_airport_name VARCHAR(100) NOT NULL, departure_time DATETIME DEFAULT NULL, arrival_time DATETIME DEFAULT NULL, duration_per_hours INT NOT NULL, available_seats INT NOT NULL, flight_base_price DOUBLE PRECISION NOT NULL, flight_status VARCHAR(255) NOT NULL, departure_country VARCHAR(150) NOT NULL, arrival_country VARCHAR(150) NOT NULL, INDEX flights_ibfk_1 (airline_name), PRIMARY KEY(id_flight)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE hotel (id_hotel_h INT AUTO_INCREMENT NOT NULL, nom_h VARCHAR(255) NOT NULL, adresse_h VARCHAR(255) NOT NULL, ville_h VARCHAR(255) NOT NULL, pays_h VARCHAR(255) NOT NULL, categorie_h INT NOT NULL, services_h VARCHAR(255) NOT NULL, coordonnees_h VARCHAR(255) NOT NULL, avis_h VARCHAR(255) NOT NULL, PRIMARY KEY(id_hotel_h)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE private_car (id INT AUTO_INCREMENT NOT NULL, id_driver INT DEFAULT NULL, brand VARCHAR(30) NOT NULL, model VARCHAR(30) NOT NULL, num_place INT NOT NULL, image VARCHAR(500) NOT NULL, INDEX fk_id_driver_private_car (id_driver), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation_hotel (id_reservation_h INT AUTO_INCREMENT NOT NULL, id_chambre_j INT DEFAULT NULL, date_checkin_h DATE NOT NULL, date_checkout_h DATE NOT NULL, nombre_chambres_h INT NOT NULL, statut_h VARCHAR(255) NOT NULL, moyen_Paiement_h VARCHAR(255) NOT NULL, INDEX id_chambre_j (id_chambre_j), PRIMARY KEY(id_reservation_h)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, commentaire TEXT DEFAULT NULL, note INT NOT NULL, dateReview DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, activityId INT NOT NULL, userId INT DEFAULT NULL, INDEX review_ibfk_1 (activityId), INDEX fk_review_user (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE tickets (ticket_id INT AUTO_INCREMENT NOT NULL, id_flight INT DEFAULT NULL, passenger_id INT DEFAULT NULL, selected_user INT DEFAULT NULL, seat_number VARCHAR(15) NOT NULL, ticket_class VARCHAR(255) NOT NULL, ticket_price DOUBLE PRECISION DEFAULT NULL, ticket_status VARCHAR(255) NOT NULL, ticket_booking_date DATETIME DEFAULT CURRENT_TIMESTAMP, passenger_email VARCHAR(100) NOT NULL, INDEX flight_id (id_flight), INDEX passenger_id (passenger_id), INDEX selected_user (selected_user), PRIMARY KEY(ticket_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE typeactivity (id INT AUTO_INCREMENT NOT NULL, nomEvenement VARCHAR(30) NOT NULL, nomType VARCHAR(30) NOT NULL, UNIQUE INDEX nomType (nomType), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, genre VARCHAR(255) DEFAULT NULL, date_naissance DATE DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, roles VARCHAR(50) DEFAULT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, phone_number VARCHAR(20) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, statut VARCHAR(255) NOT NULL, privileges VARCHAR(255) DEFAULT NULL, poste VARCHAR(255) DEFAULT NULL, departement VARCHAR(255) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expiry DATETIME DEFAULT NULL, UNIQUE INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_activity (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, activity_id INT DEFAULT NULL, INDEX user_id (user_id), INDEX activity_id (activity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT FK_F67D02E9C3C6F69F FOREIGN KEY (car_id) REFERENCES private_car (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer ADD CONSTRAINT FK_F67D02E934ECB4E6 FOREIGN KEY (route_id) REFERENCES car_route (id)
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
            ALTER TABLE flights ADD CONSTRAINT FK_FC74B5EA22608261 FOREIGN KEY (airline_name) REFERENCES airlines (airline_name)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car ADD CONSTRAINT FK_A54C9923751C934 FOREIGN KEY (id_driver) REFERENCES car_driver (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel ADD CONSTRAINT FK_402C8E7E65EB791F FOREIGN KEY (id_chambre_j) REFERENCES chambre (id_chambre_h)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4E46053E3 FOREIGN KEY (id_flight) REFERENCES flights (id_flight)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT FK_54469DF44502E565 FOREIGN KEY (passenger_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets ADD CONSTRAINT FK_54469DF4C430DB51 FOREIGN KEY (selected_user) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY FK_F67D02E9C3C6F69F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE car_offer DROP FOREIGN KEY FK_F67D02E934ECB4E6
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
            ALTER TABLE flights DROP FOREIGN KEY FK_FC74B5EA22608261
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE private_car DROP FOREIGN KEY FK_A54C9923751C934
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_hotel DROP FOREIGN KEY FK_402C8E7E65EB791F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4E46053E3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF44502E565
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tickets DROP FOREIGN KEY FK_54469DF4C430DB51
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE activity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE airlines
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE car_driver
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE car_offer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE car_reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE car_route
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE chambre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE flight_reservations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE flights
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE hotel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE private_car
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation_hotel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE review
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE tickets
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE typeactivity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_activity
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
