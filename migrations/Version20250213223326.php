<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213223326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE teoria_lista_obecnosci_kurs (teoria_lista_obecnosci_id INT NOT NULL, kurs_id INT NOT NULL, INDEX IDX_C6EE8F00D3AD7DA4 (teoria_lista_obecnosci_id), INDEX IDX_C6EE8F002CAAFBEC (kurs_id), PRIMARY KEY(teoria_lista_obecnosci_id, kurs_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs ADD CONSTRAINT FK_C6EE8F00D3AD7DA4 FOREIGN KEY (teoria_lista_obecnosci_id) REFERENCES teoria_lista_obecnosci (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs ADD CONSTRAINT FK_C6EE8F002CAAFBEC FOREIGN KEY (kurs_id) REFERENCES kurs (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs DROP FOREIGN KEY FK_C6EE8F00D3AD7DA4');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci_kurs DROP FOREIGN KEY FK_C6EE8F002CAAFBEC');
        $this->addSql('DROP TABLE teoria_lista_obecnosci_kurs');
    }
}
