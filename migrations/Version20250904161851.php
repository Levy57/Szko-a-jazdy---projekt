<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250904161851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car_category (car_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_897A2CC5C3C6F69F (car_id), INDEX IDX_897A2CC512469DE2 (category_id), PRIMARY KEY(car_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_category (user_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E6C1FDC1A76ED395 (user_id), INDEX IDX_E6C1FDC112469DE2 (category_id), PRIMARY KEY(user_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE car_category ADD CONSTRAINT FK_897A2CC5C3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car_category ADD CONSTRAINT FK_897A2CC512469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE car ADD condition_id INT NOT NULL, DROP `condition`, DROP categories');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D887793B6 FOREIGN KEY (condition_id) REFERENCES car_condition (id)');
        $this->addSql('CREATE INDEX IDX_773DE69D887793B6 ON car (condition_id)');
        $this->addSql('ALTER TABLE course ADD category_id INT NOT NULL, DROP category');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB912469DE2 ON course (category_id)');
        $this->addSql('ALTER TABLE user DROP categories');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_category DROP FOREIGN KEY FK_897A2CC5C3C6F69F');
        $this->addSql('ALTER TABLE car_category DROP FOREIGN KEY FK_897A2CC512469DE2');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC1A76ED395');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC112469DE2');
        $this->addSql('DROP TABLE car_category');
        $this->addSql('DROP TABLE user_category');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D887793B6');
        $this->addSql('DROP INDEX IDX_773DE69D887793B6 ON car');
        $this->addSql('ALTER TABLE car ADD `condition` VARCHAR(255) NOT NULL, ADD categories LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', DROP condition_id');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB912469DE2');
        $this->addSql('DROP INDEX IDX_169E6FB912469DE2 ON course');
        $this->addSql('ALTER TABLE course ADD category VARCHAR(255) NOT NULL, DROP category_id');
        $this->addSql('ALTER TABLE user ADD categories LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
