<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413191332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE document_historique (id INT AUTO_INCREMENT NOT NULL, id_document INT NOT NULL, nom_document VARCHAR(255) NOT NULL, action VARCHAR(50) NOT NULL, anciennes_valeurs JSON DEFAULT NULL, nouvelles_valeurs JSON DEFAULT NULL, date_action DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE categorie_document CHANGE libelle libelle VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY `document_ibfk_1`');
        $this->addSql('ALTER TABLE document ADD tags JSON DEFAULT NULL, CHANGE nom_document nom_document VARCHAR(255) NOT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(500) NOT NULL, CHANGE date_ajout date_ajout DATE NOT NULL');
        $this->addSql('DROP INDEX id_categorie ON document');
        $this->addSql('CREATE INDEX IDX_D8698A76C9486A13 ON document (id_categorie)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (id_categorie) REFERENCES categorie_document (id_categorie)');
        $this->addSql('ALTER TABLE document_archive CHANGE nom_document nom_document VARCHAR(255) NOT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(500) NOT NULL, CHANGE date_archivage date_archivage DATETIME NOT NULL, CHANGE raison raison VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE document_historique');
        $this->addSql('ALTER TABLE categorie_document CHANGE libelle libelle VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76C9486A13');
        $this->addSql('ALTER TABLE document DROP tags, CHANGE nom_document nom_document VARCHAR(150) NOT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(255) NOT NULL, CHANGE date_ajout date_ajout DATE DEFAULT NULL');
        $this->addSql('DROP INDEX idx_d8698a76c9486a13 ON document');
        $this->addSql('CREATE INDEX id_categorie ON document (id_categorie)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76C9486A13 FOREIGN KEY (id_categorie) REFERENCES categorie_document (id_categorie)');
        $this->addSql('ALTER TABLE document_archive CHANGE nom_document nom_document VARCHAR(255) DEFAULT NULL, CHANGE chemin_fichier chemin_fichier VARCHAR(500) DEFAULT NULL, CHANGE date_archivage date_archivage DATETIME DEFAULT CURRENT_TIMESTAMP, CHANGE raison raison VARCHAR(255) DEFAULT \'Expiré\'');
    }
}
