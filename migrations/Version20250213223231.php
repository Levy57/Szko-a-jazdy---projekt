<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213223231 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE83E6D49D31');
        $this->addSql('DROP INDEX IDX_F0BEBE83E6D49D31 ON teoria_lista_obecnosci');
        $this->addSql('DROP INDEX UNIQ_USER ON teoria_lista_obecnosci');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci CHANGE id_teoria_id teoria_id INT NOT NULL');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE8366E12116 FOREIGN KEY (teoria_id) REFERENCES teoria (id)');
        $this->addSql('CREATE INDEX IDX_F0BEBE8366E12116 ON teoria_lista_obecnosci (teoria_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER ON teoria_lista_obecnosci (teoria_id, praktykant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE teoria_lista_obecnosci DROP FOREIGN KEY FK_F0BEBE8366E12116');
        $this->addSql('DROP INDEX IDX_F0BEBE8366E12116 ON teoria_lista_obecnosci');
        $this->addSql('DROP INDEX UNIQ_USER ON teoria_lista_obecnosci');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci CHANGE teoria_id id_teoria_id INT NOT NULL');
        $this->addSql('ALTER TABLE teoria_lista_obecnosci ADD CONSTRAINT FK_F0BEBE83E6D49D31 FOREIGN KEY (id_teoria_id) REFERENCES teoria (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F0BEBE83E6D49D31 ON teoria_lista_obecnosci (id_teoria_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER ON teoria_lista_obecnosci (id_teoria_id, praktykant_id)');
    }
}
