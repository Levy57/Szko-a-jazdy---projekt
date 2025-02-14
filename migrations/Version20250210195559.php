<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210195559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE kurs_harmonogram (id INT AUTO_INCREMENT NOT NULL, instruktor_id INT NOT NULL, kurs_id INT NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, INDEX IDX_53A0EBA0BFA08FF4 (instruktor_id), INDEX IDX_53A0EBA02CAAFBEC (kurs_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kurs_harmonogram ADD CONSTRAINT FK_53A0EBA0BFA08FF4 FOREIGN KEY (instruktor_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE kurs_harmonogram ADD CONSTRAINT FK_53A0EBA02CAAFBEC FOREIGN KEY (kurs_id) REFERENCES kurs (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE kurs_harmonogram DROP FOREIGN KEY FK_53A0EBA0BFA08FF4');
        $this->addSql('ALTER TABLE kurs_harmonogram DROP FOREIGN KEY FK_53A0EBA02CAAFBEC');
        $this->addSql('DROP TABLE kurs_harmonogram');
    }
}
