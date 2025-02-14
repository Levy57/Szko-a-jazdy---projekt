<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208151852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kurs (id INT AUTO_INCREMENT NOT NULL, praktykant_id INT NOT NULL, instruktor_id INT DEFAULT NULL, kategoria VARCHAR(255) NOT NULL, teoria TINYINT(1) NOT NULL, teoria_godziny INT NOT NULL, praktyka_godziny INT NOT NULL, INDEX IDX_4B5C3E57CABAAD55 (praktykant_id), INDEX IDX_4B5C3E57BFA08FF4 (instruktor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kurs ADD CONSTRAINT FK_4B5C3E57CABAAD55 FOREIGN KEY (praktykant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE kurs ADD CONSTRAINT FK_4B5C3E57BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kurs DROP FOREIGN KEY FK_4B5C3E57CABAAD55');
        $this->addSql('ALTER TABLE kurs DROP FOREIGN KEY FK_4B5C3E57BFA08FF4');
        $this->addSql('DROP TABLE kurs');
    }
}
