<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403192434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY `planning_ibfk_1`');
        $this->addSql('ALTER TABLE voyage DROP FOREIGN KEY `voyage_ibfk_1`');
        $this->addSql('DROP TABLE activite');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE destination');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('DROP TABLE planning');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE voyage');
        $this->addSql('ALTER TABLE categorie_document MODIFY id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE categorie_document CHANGE libelle libelle VARCHAR(255) NOT NULL, CHANGE id_categorie id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY `document_ibfk_1`');
        $this->addSql('DROP INDEX id_categorie ON document');
        $this->addSql('ALTER TABLE document MODIFY id_document INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD categorie_id INT NOT NULL, DROP id_categorie, CHANGE nom_document nom_document VARCHAR(255) NOT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(500) NOT NULL, CHANGE date_ajout date_ajout DATE NOT NULL, CHANGE date_expiration date_expiration DATE NOT NULL, CHANGE id_document id INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_document (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76BCF5E72D ON document (categorie_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activite (id_activite INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, categorie VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, lieu VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prix NUMERIC(8, 2) DEFAULT NULL, PRIMARY KEY (id_activite)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE categorie (id_cat INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(100) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, description VARCHAR(255) CHARACTER SET latin1 DEFAULT NULL COLLATE `latin1_swedish_ci`, icone_url VARCHAR(255) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, PRIMARY KEY (id_cat)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE depense (id_dep INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) CHARACTER SET latin1 NOT NULL COLLATE `latin1_swedish_ci`, montant NUMERIC(10, 2) NOT NULL, date_depense DATE NOT NULL, id_categorie INT NOT NULL, id_voyage INT DEFAULT NULL, INDEX fk_dep_voyage (id_voyage), INDEX fk_dep_cat (id_categorie), PRIMARY KEY (id_dep)) DEFAULT CHARACTER SET latin1 COLLATE `latin1_swedish_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE destination (id_destination INT AUTO_INCREMENT NOT NULL, pays VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, ville VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, continent VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id_destination)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE offre (id_offre INT NOT NULL, titre VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prix DOUBLE PRECISION DEFAULT NULL, duree INT DEFAULT NULL, id_service INT DEFAULT NULL, INDEX id_service (id_service), PRIMARY KEY (id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE paiement (id INT NOT NULL, reference VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, montant NUMERIC(10, 2) NOT NULL, methode VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, id_reservation INT NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'en attente\' NOT NULL COLLATE `utf8mb4_general_ci`, devise VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT \'TND\' NOT NULL COLLATE `utf8mb4_general_ci`, date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX id_reservation (id_reservation), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE planning (id_planning INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, id_activite INT NOT NULL, date_activite DATE NOT NULL, heure_debut TIME NOT NULL, duree INT NOT NULL, INDEX id_activite (id_activite), PRIMARY KEY (id_planning)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reservation (id INT NOT NULL, date_reservation DATE NOT NULL, statut VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT \'en attente\' NOT NULL COLLATE `utf8mb4_general_ci`, nb_personnes INT NOT NULL, id_user INT DEFAULT 1 NOT NULL, id_voyage INT DEFAULT 23 NOT NULL, id_utilisateur INT DEFAULT 1 NOT NULL, type VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, lieu VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prix_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL, INDEX fk_res_user (id_user), INDEX fk_res_voyage (id_voyage), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE service (id_service INT NOT NULL, nom_service VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, categorie VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, PRIMARY KEY (id_service)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, password VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, telephone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, photo_profil VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, type_utilisateur VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'VOYAGEUR\' NOT NULL COLLATE `utf8mb4_general_ci`, is_verified TINYINT DEFAULT 0, verification_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, verification_expiry DATETIME DEFAULT NULL, UNIQUE INDEX email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE voyage (id_voyage INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, date_debut DATE NOT NULL, date_fin DATE NOT NULL, prix DOUBLE PRECISION NOT NULL, nb_places INT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, id_destination INT NOT NULL, INDEX id_destination (id_destination), PRIMARY KEY (id_voyage)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT `planning_ibfk_1` FOREIGN KEY (id_activite) REFERENCES activite (id_activite) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voyage ADD CONSTRAINT `voyage_ibfk_1` FOREIGN KEY (id_destination) REFERENCES destination (id_destination)');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE categorie_document MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE categorie_document CHANGE libelle libelle VARCHAR(100) NOT NULL, CHANGE id id_categorie INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_categorie)');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76BCF5E72D');
        $this->addSql('DROP INDEX IDX_D8698A76BCF5E72D ON document');
        $this->addSql('ALTER TABLE document MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE document ADD id_categorie INT DEFAULT NULL, DROP categorie_id, CHANGE nom_document nom_document VARCHAR(150) NOT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(255) NOT NULL, CHANGE date_ajout date_ajout DATE DEFAULT NULL, CHANGE date_expiration date_expiration DATE DEFAULT NULL, CHANGE id id_document INT AUTO_INCREMENT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_document)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (id_categorie) REFERENCES categorie_document (id_categorie)');
        $this->addSql('CREATE INDEX id_categorie ON document (id_categorie)');
    }
}
