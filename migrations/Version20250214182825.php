<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214182825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, tekst VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kurs (id INT AUTO_INCREMENT NOT NULL, praktykant_id INT NOT NULL, instruktor_id INT DEFAULT NULL, kategoria VARCHAR(255) NOT NULL, teoria TINYINT(1) NOT NULL, teoria_godziny INT NOT NULL, praktyka_godziny INT NOT NULL, start_kurs DATE DEFAULT NULL, end_kurs DATE DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_4B5C3E57CABAAD55 (praktykant_id), INDEX IDX_4B5C3E57BFA08FF4 (instruktor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kurs_harmonogram (id INT AUTO_INCREMENT NOT NULL, instruktor_id INT NOT NULL, kurs_id INT NOT NULL, start DATETIME NOT NULL, czas_trwania INT NOT NULL, komentarz VARCHAR(255) DEFAULT NULL, INDEX IDX_53A0EBA0BFA08FF4 (instruktor_id), INDEX IDX_53A0EBA02CAAFBEC (kurs_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pojazd (id INT AUTO_INCREMENT NOT NULL, nazwa VARCHAR(255) DEFAULT NULL, marka VARCHAR(255) NOT NULL, rok INT NOT NULL, stan VARCHAR(255) NOT NULL, kategoria LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', vin VARCHAR(255) DEFAULT NULL, komentarz VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teoria (id INT AUTO_INCREMENT NOT NULL, instruktor_id INT NOT NULL, start DATETIME NOT NULL, czas_trwania INT NOT NULL, temat VARCHAR(255) DEFAULT NULL, opis VARCHAR(255) DEFAULT NULL, INDEX IDX_CA19CC02BFA08FF4 (instruktor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teoria_lista_obecnosci (id INT AUTO_INCREMENT NOT NULL, teoria_id INT NOT NULL, praktykant_id INT NOT NULL, INDEX IDX_F0BEBE8366E12116 (teoria_id), INDEX IDX_F0BEBE83CABAAD55 (praktykant_id), UNIQUE INDEX UNIQ_USER (teoria_id, praktykant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teoria_lista_obecnosci_kurs (teoria_lista_obecnosci_id INT NOT NULL, kurs_id INT NOT NULL, INDEX IDX_C6EE8F00D3AD7DA4 (teoria_lista_obecnosci_id), INDEX IDX_C6EE8F002CAAFBEC (kurs_id), PRIMARY KEY(teoria_lista_obecnosci_id, kurs_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, imie VARCHAR(255) NOT NULL, nazwisko VARCHAR(255) NOT NULL, numer_telefonu VARCHAR(255) NOT NULL, kategoria_uprawnien LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kurs ADD CONSTRAINT FK_4B5C3E57CABAAD55 FOREIGN KEY (praktykant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE kurs ADD CONSTRAINT FK_4B5C3E57BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE kurs_harmonogram ADD CONSTRAINT FK_53A0EBA0BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE kurs_harmonogram ADD CONSTRAINT FK_53A0EBA02CAAFBEC FOREIGN KEY (kurs_id) REFERENCES kurs (id)');
        $this->addSql('ALTER TABLE teoria ADD CONSTRAINT FK_CA19CC02BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE8366E12116 FOREIGN KEY (teoria_id) REFERENCES teoria (id)');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE83CABAAD55 FOREIGN KEY (praktykant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs ADD CONSTRAINT FK_C6EE8F00D3AD7DA4 FOREIGN KEY (teoria_lista_obecnosci_id) REFERENCES teoria_lista_obecnosci (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs ADD CONSTRAINT FK_C6EE8F002CAAFBEC FOREIGN KEY (kurs_id) REFERENCES kurs (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kurs DROP FOREIGN KEY FK_4B5C3E57CABAAD55');
        $this->addSql('ALTER TABLE kurs DROP FOREIGN KEY FK_4B5C3E57BFA08FF4');
        $this->addSql('ALTER TABLE kurs_harmonogram DROP FOREIGN KEY FK_53A0EBA0BFA08FF4');
        $this->addSql('ALTER TABLE kurs_harmonogram DROP FOREIGN KEY FK_53A0EBA02CAAFBEC');
        $this->addSql('ALTER TABLE teoria DROP FOREIGN KEY FK_CA19CC02BFA08FF4');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE8366E12116');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE83CABAAD55');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs DROP FOREIGN KEY FK_C6EE8F00D3AD7DA4');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs DROP FOREIGN KEY FK_C6EE8F002CAAFBEC');
        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE kurs');
        $this->addSql('DROP TABLE kurs_harmonogram');
        $this->addSql('DROP TABLE pojazd');
        $this->addSql('DROP TABLE teoria');
        $this->addSql('DROP TABLE teoria_lista_obecnosci');
        $this->addSql('DROP TABLE teoria_lista_obecnosci_kurs');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
