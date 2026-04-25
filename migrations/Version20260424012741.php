<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260424012741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, rendez_vous_id INT NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, UNIQUE INDEX UNIQ_964685A691EF7EAA (rendez_vous_id), INDEX IDX_964685A66B899279 (patient_id), INDEX IDX_964685A64F31A84 (medecin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE consultation_type (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, duree_en_min INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE disponibilite (id INT AUTO_INCREMENT NOT NULL, jour_semaine VARCHAR(20) NOT NULL, heure_debut TIME NOT NULL, heure_fin TIME NOT NULL, duree_creneau_en_min INT NOT NULL, medecin_id INT NOT NULL, INDEX IDX_2CBACE2F4F31A84 (medecin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE document_medical (id INT AUTO_INCREMENT NOT NULL, type_document VARCHAR(50) NOT NULL, file_path VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, uploaded_at DATETIME NOT NULL, consultation_id INT NOT NULL, INDEX IDX_D3B4A18662FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dossier_patient (id INT AUTO_INCREMENT NOT NULL, antecedents LONGTEXT DEFAULT NULL, maladies_chroniques LONGTEXT DEFAULT NULL, allergies LONGTEXT DEFAULT NULL, groupe_sanguin VARCHAR(5) DEFAULT NULL, patient_id INT NOT NULL, UNIQUE INDEX UNIQ_58803ED36B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE mutuelle (id INT AUTO_INCREMENT NOT NULL, organisme VARCHAR(100) NOT NULL, numero_adherent VARCHAR(50) DEFAULT NULL, taux_remboursement DOUBLE PRECISION DEFAULT NULL, patient_id INT DEFAULT NULL, INDEX IDX_88B831EE6B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, contenu LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, consultation_id INT NOT NULL, INDEX IDX_924B326C62FF6CDF (consultation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, date_de_naissance DATE NOT NULL, telephone VARCHAR(20) NOT NULL, adresse VARCHAR(50) NOT NULL, numero_secu VARCHAR(50) DEFAULT NULL, carte_vitale_path VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_1ADAD7EBA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE professionnel_sante (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, specialite VARCHAR(100) NOT NULL, numero_rpps VARCHAR(100) NOT NULL, telephone VARCHAR(20) NOT NULL, adresse VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, code_postal VARCHAR(10) NOT NULL, secteur VARCHAR(1) NOT NULL, conventionne_secu TINYINT NOT NULL, mode_de_paiement JSON DEFAULT NULL, horaires JSON DEFAULT NULL, description LONGTEXT DEFAULT NULL, photo_path VARCHAR(255) DEFAULT NULL, statut VARCHAR(20) NOT NULL, justificatif_path VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_5CF3C71EA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE professionnel_sante_consultation_type (professionnel_sante_id INT NOT NULL, consultation_type_id INT NOT NULL, INDEX IDX_FA5B5A0CD0F6A939 (professionnel_sante_id), INDEX IDX_FA5B5A0C804F7D71 (consultation_type_id), PRIMARY KEY (professionnel_sante_id, consultation_type_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, date_heure DATETIME NOT NULL, statut VARCHAR(20) NOT NULL, motif LONGTEXT DEFAULT NULL, ordonnance_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, consultation_type_id INT NOT NULL, INDEX IDX_65E8AA0A6B899279 (patient_id), INDEX IDX_65E8AA0A4F31A84 (medecin_id), INDEX IDX_65E8AA0A804F7D71 (consultation_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A691EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES professionnel_sante (id)');
        $this->addSql('ALTER TABLE disponibilite ADD CONSTRAINT FK_2CBACE2F4F31A84 FOREIGN KEY (medecin_id) REFERENCES professionnel_sante (id)');
        $this->addSql('ALTER TABLE document_medical ADD CONSTRAINT FK_D3B4A18662FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE dossier_patient ADD CONSTRAINT FK_58803ED36B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE mutuelle ADD CONSTRAINT FK_88B831EE6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE professionnel_sante ADD CONSTRAINT FK_5CF3C71EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE professionnel_sante_consultation_type ADD CONSTRAINT FK_FA5B5A0CD0F6A939 FOREIGN KEY (professionnel_sante_id) REFERENCES professionnel_sante (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE professionnel_sante_consultation_type ADD CONSTRAINT FK_FA5B5A0C804F7D71 FOREIGN KEY (consultation_type_id) REFERENCES consultation_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES professionnel_sante (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A804F7D71 FOREIGN KEY (consultation_type_id) REFERENCES consultation_type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A691EF7EAA');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE disponibilite DROP FOREIGN KEY FK_2CBACE2F4F31A84');
        $this->addSql('ALTER TABLE document_medical DROP FOREIGN KEY FK_D3B4A18662FF6CDF');
        $this->addSql('ALTER TABLE dossier_patient DROP FOREIGN KEY FK_58803ED36B899279');
        $this->addSql('ALTER TABLE mutuelle DROP FOREIGN KEY FK_88B831EE6B899279');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C62FF6CDF');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBA76ED395');
        $this->addSql('ALTER TABLE professionnel_sante DROP FOREIGN KEY FK_5CF3C71EA76ED395');
        $this->addSql('ALTER TABLE professionnel_sante_consultation_type DROP FOREIGN KEY FK_FA5B5A0CD0F6A939');
        $this->addSql('ALTER TABLE professionnel_sante_consultation_type DROP FOREIGN KEY FK_FA5B5A0C804F7D71');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A804F7D71');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE consultation_type');
        $this->addSql('DROP TABLE disponibilite');
        $this->addSql('DROP TABLE document_medical');
        $this->addSql('DROP TABLE dossier_patient');
        $this->addSql('DROP TABLE mutuelle');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE professionnel_sante');
        $this->addSql('DROP TABLE professionnel_sante_consultation_type');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE `user`');
    }
}
