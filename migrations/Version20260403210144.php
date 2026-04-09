<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403210144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY fk_act_voyage');
        $this->addSql('ALTER TABLE activite CHANGE id_activite id_activite INT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE categorie categorie VARCHAR(50) NOT NULL, CHANGE lieu lieu VARCHAR(100) NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT FK_B875551519AA3CB8 FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activite RENAME INDEX fk_act_voyage TO IDX_B875551519AA3CB8');
        $this->addSql('ALTER TABLE categorie CHANGE id_cat id_cat INT NOT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE categorie_document CHANGE id_categorie id_categorie INT NOT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY fk_dep_voyage');
        $this->addSql('ALTER TABLE depense CHANGE id_dep id_dep INT NOT NULL, CHANGE montant montant DOUBLE PRECISION NOT NULL, CHANGE id_categorie id_categorie INT DEFAULT NULL');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT FK_3405975719AA3CB8 FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE depense RENAME INDEX fk_dep_cat TO IDX_34059757C9486A13');
        $this->addSql('ALTER TABLE depense RENAME INDEX fk_dep_voyage TO IDX_3405975719AA3CB8');
        $this->addSql('ALTER TABLE destination CHANGE id_destination id_destination INT NOT NULL, CHANGE continent continent VARCHAR(50) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY document_ibfk_1');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY fk_doc_user');
        $this->addSql('ALTER TABLE document CHANGE id_document id_document INT NOT NULL, CHANGE date_ajout date_ajout DATE NOT NULL, CHANGE date_expiration date_expiration DATE NOT NULL');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C9486A13 FOREIGN KEY (id_categorie) REFERENCES categorie_document (id_categorie) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766B3CA4B FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE document RENAME INDEX id_categorie TO IDX_D8698A76C9486A13');
        $this->addSql('ALTER TABLE document RENAME INDEX fk_doc_user TO IDX_D8698A766B3CA4B');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY offre_ibfk_1');
        $this->addSql('ALTER TABLE offre CHANGE id_offre id_offre INT NOT NULL, CHANGE titre titre VARCHAR(100) NOT NULL, CHANGE prix prix DOUBLE PRECISION NOT NULL, CHANGE duree duree INT NOT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT FK_AF86866F3F0033A2 FOREIGN KEY (id_service) REFERENCES service (id_service) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offre RENAME INDEX id_service TO IDX_AF86866F3F0033A2');
        $this->addSql('ALTER TABLE paiement CHANGE id id INT NOT NULL, CHANGE montant montant DOUBLE PRECISION NOT NULL, CHANGE id_reservation id_reservation INT DEFAULT NULL, CHANGE statut statut VARCHAR(50) NOT NULL, CHANGE devise devise VARCHAR(10) NOT NULL, CHANGE date_paiement date_paiement DATETIME NOT NULL');
        $this->addSql('ALTER TABLE paiement RENAME INDEX id_reservation TO IDX_B1DC7A1E5ADA84A2');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY planning_ibfk_1');
        $this->addSql('ALTER TABLE planning CHANGE id_planning id_planning INT NOT NULL, CHANGE id_activite id_activite INT DEFAULT NULL, CHANGE heure_debut heure_debut VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT FK_D499BFF6E8AEB980 FOREIGN KEY (id_activite) REFERENCES activite (id_activite) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planning RENAME INDEX id_activite TO IDX_D499BFF6E8AEB980');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY fk_res_voyage');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY fk_res_user');
        $this->addSql('ALTER TABLE reservation CHANGE id id INT NOT NULL, CHANGE statut statut VARCHAR(50) NOT NULL, CHANGE id_user id_user INT DEFAULT NULL, CHANGE id_voyage id_voyage INT DEFAULT NULL, CHANGE id_utilisateur id_utilisateur INT NOT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE lieu lieu VARCHAR(100) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE prix_total prix_total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C849556B3CA4B FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495519AA3CB8 FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation RENAME INDEX fk_res_user TO IDX_42C849556B3CA4B');
        $this->addSql('ALTER TABLE reservation RENAME INDEX fk_res_voyage TO IDX_42C8495519AA3CB8');
        $this->addSql('ALTER TABLE service CHANGE id_service id_service INT NOT NULL, CHANGE nom_service nom_service VARCHAR(100) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE categorie categorie VARCHAR(100) NOT NULL');
        $this->addSql('DROP INDEX email ON users');
        $this->addSql('ALTER TABLE users CHANGE id id INT NOT NULL, CHANGE telephone telephone VARCHAR(20) NOT NULL, CHANGE photo_profil photo_profil VARCHAR(255) NOT NULL, CHANGE type_utilisateur type_utilisateur VARCHAR(20) NOT NULL, CHANGE is_verified is_verified TINYINT(1) NOT NULL, CHANGE verification_token verification_token VARCHAR(255) NOT NULL, CHANGE verification_expiry verification_expiry DATETIME NOT NULL');
        $this->addSql('ALTER TABLE voyage DROP FOREIGN KEY voyage_ibfk_1');
        $this->addSql('ALTER TABLE voyage CHANGE id_voyage id_voyage INT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE image image VARCHAR(255) NOT NULL, CHANGE id_destination id_destination INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voyage ADD CONSTRAINT FK_3F9D895526D4F35D FOREIGN KEY (id_destination) REFERENCES destination (id_destination) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE voyage RENAME INDEX id_destination TO IDX_3F9D895526D4F35D');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE activite DROP FOREIGN KEY FK_B875551519AA3CB8');
        $this->addSql('ALTER TABLE activite CHANGE id_activite id_activite INT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE categorie categorie VARCHAR(50) DEFAULT \'NULL\', CHANGE lieu lieu VARCHAR(100) DEFAULT \'NULL\', CHANGE prix prix NUMERIC(8, 2) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE activite ADD CONSTRAINT fk_act_voyage FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage)');
        $this->addSql('ALTER TABLE activite RENAME INDEX idx_b875551519aa3cb8 TO fk_act_voyage');
        $this->addSql('ALTER TABLE categorie CHANGE id_cat id_cat INT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE categorie_document CHANGE id_categorie id_categorie INT AUTO_INCREMENT NOT NULL, CHANGE description description VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE depense DROP FOREIGN KEY FK_3405975719AA3CB8');
        $this->addSql('ALTER TABLE depense CHANGE id_dep id_dep INT AUTO_INCREMENT NOT NULL, CHANGE montant montant NUMERIC(10, 2) NOT NULL, CHANGE id_categorie id_categorie INT NOT NULL');
        $this->addSql('ALTER TABLE depense ADD CONSTRAINT fk_dep_voyage FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage)');
        $this->addSql('ALTER TABLE depense RENAME INDEX idx_34059757c9486a13 TO fk_dep_cat');
        $this->addSql('ALTER TABLE depense RENAME INDEX idx_3405975719aa3cb8 TO fk_dep_voyage');
        $this->addSql('ALTER TABLE destination CHANGE id_destination id_destination INT AUTO_INCREMENT NOT NULL, CHANGE continent continent VARCHAR(50) DEFAULT \'NULL\', CHANGE description description TEXT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76C9486A13');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766B3CA4B');
        $this->addSql('ALTER TABLE document CHANGE id_document id_document INT AUTO_INCREMENT NOT NULL, CHANGE date_ajout date_ajout DATE DEFAULT \'NULL\', CHANGE date_expiration date_expiration DATE DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT document_ibfk_1 FOREIGN KEY (id_categorie) REFERENCES categorie_document (id_categorie)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT fk_doc_user FOREIGN KEY (id_user) REFERENCES users (id)');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_d8698a766b3ca4b TO fk_doc_user');
        $this->addSql('ALTER TABLE document RENAME INDEX idx_d8698a76c9486a13 TO id_categorie');
        $this->addSql('ALTER TABLE offre DROP FOREIGN KEY FK_AF86866F3F0033A2');
        $this->addSql('ALTER TABLE offre CHANGE id_offre id_offre INT AUTO_INCREMENT NOT NULL, CHANGE titre titre VARCHAR(100) DEFAULT \'NULL\', CHANGE prix prix DOUBLE PRECISION DEFAULT \'NULL\', CHANGE duree duree INT DEFAULT NULL');
        $this->addSql('ALTER TABLE offre ADD CONSTRAINT offre_ibfk_1 FOREIGN KEY (id_service) REFERENCES service (id_service)');
        $this->addSql('ALTER TABLE offre RENAME INDEX idx_af86866f3f0033a2 TO id_service');
        $this->addSql('ALTER TABLE paiement CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE montant montant NUMERIC(10, 2) NOT NULL, CHANGE statut statut VARCHAR(50) DEFAULT \'\'\'en attente\'\'\' NOT NULL, CHANGE devise devise VARCHAR(10) DEFAULT \'\'\'TND\'\'\' NOT NULL, CHANGE date_paiement date_paiement DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE id_reservation id_reservation INT NOT NULL');
        $this->addSql('ALTER TABLE paiement RENAME INDEX idx_b1dc7a1e5ada84a2 TO id_reservation');
        $this->addSql('ALTER TABLE planning DROP FOREIGN KEY FK_D499BFF6E8AEB980');
        $this->addSql('ALTER TABLE planning CHANGE id_planning id_planning INT AUTO_INCREMENT NOT NULL, CHANGE heure_debut heure_debut TIME NOT NULL, CHANGE id_activite id_activite INT NOT NULL');
        $this->addSql('ALTER TABLE planning ADD CONSTRAINT planning_ibfk_1 FOREIGN KEY (id_activite) REFERENCES activite (id_activite) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('ALTER TABLE planning RENAME INDEX idx_d499bff6e8aeb980 TO id_activite');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C849556B3CA4B');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495519AA3CB8');
        $this->addSql('ALTER TABLE reservation CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE statut statut VARCHAR(50) DEFAULT \'\'\'en attente\'\'\' NOT NULL, CHANGE id_utilisateur id_utilisateur INT DEFAULT 1 NOT NULL, CHANGE type type VARCHAR(50) DEFAULT \'NULL\', CHANGE lieu lieu VARCHAR(100) DEFAULT \'NULL\', CHANGE description description TEXT DEFAULT NULL, CHANGE prix_total prix_total DOUBLE PRECISION DEFAULT \'0\' NOT NULL, CHANGE id_user id_user INT DEFAULT 1 NOT NULL, CHANGE id_voyage id_voyage INT DEFAULT 23 NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_res_voyage FOREIGN KEY (id_voyage) REFERENCES voyage (id_voyage)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT fk_res_user FOREIGN KEY (id_user) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservation RENAME INDEX idx_42c849556b3ca4b TO fk_res_user');
        $this->addSql('ALTER TABLE reservation RENAME INDEX idx_42c8495519aa3cb8 TO fk_res_voyage');
        $this->addSql('ALTER TABLE service CHANGE id_service id_service INT AUTO_INCREMENT NOT NULL, CHANGE nom_service nom_service VARCHAR(100) DEFAULT \'NULL\', CHANGE description description VARCHAR(255) DEFAULT \'NULL\', CHANGE categorie categorie VARCHAR(100) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE users CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE telephone telephone VARCHAR(20) DEFAULT \'NULL\', CHANGE photo_profil photo_profil VARCHAR(255) DEFAULT \'NULL\', CHANGE type_utilisateur type_utilisateur VARCHAR(20) DEFAULT \'\'\'VOYAGEUR\'\'\' NOT NULL, CHANGE is_verified is_verified TINYINT(1) DEFAULT 0, CHANGE verification_token verification_token VARCHAR(255) DEFAULT \'NULL\', CHANGE verification_expiry verification_expiry DATETIME DEFAULT \'NULL\'');
        $this->addSql('CREATE UNIQUE INDEX email ON users (email)');
        $this->addSql('ALTER TABLE voyage DROP FOREIGN KEY FK_3F9D895526D4F35D');
        $this->addSql('ALTER TABLE voyage CHANGE id_voyage id_voyage INT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT \'NULL\', CHANGE id_destination id_destination INT NOT NULL');
        $this->addSql('ALTER TABLE voyage ADD CONSTRAINT voyage_ibfk_1 FOREIGN KEY (id_destination) REFERENCES destination (id_destination)');
        $this->addSql('ALTER TABLE voyage RENAME INDEX idx_3f9d895526d4f35d TO id_destination');
    }
}
