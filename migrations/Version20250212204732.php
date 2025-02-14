<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212204732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE teoria (id INT AUTO_INCREMENT NOT NULL, instruktor_id INT NOT NULL, start DATETIME NOT NULL, czas_trwania INT NOT NULL, temat VARCHAR(255) DEFAULT NULL, opis VARCHAR(255) DEFAULT NULL, INDEX IDX_CA19CC02BFA08FF4 (instruktor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teoria_lista_obecnosci (id INT AUTO_INCREMENT NOT NULL, id_teoria_id INT NOT NULL, praktykant_id INT NOT NULL, INDEX IDX_F0BEBE83E6D49D31 (id_teoria_id), INDEX IDX_F0BEBE83CABAAD55 (praktykant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teoria ADD CONSTRAINT FK_CA19CC02BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE83E6D49D31 FOREIGN KEY (id_teoria_id) REFERENCES teoria (id)');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE83CABAAD55 FOREIGN KEY (praktykant_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teoria DROP FOREIGN KEY FK_CA19CC02BFA08FF4');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE83E6D49D31');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE83CABAAD55');
        $this->addSql('DROP TABLE teoria');
        $this->addSql('DROP TABLE teoria_lista_obecnosci');
    }
}
