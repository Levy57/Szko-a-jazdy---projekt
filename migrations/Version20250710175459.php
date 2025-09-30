<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250710175459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, brand VARCHAR(255) NOT NULL, year INT NOT NULL, `condition` VARCHAR(255) NOT NULL, categories LONGTEXT NOT NULL COMMENT '(DC2Type:simple_array)', vin VARCHAR(255) DEFAULT NULL, note VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, customer_id INT NOT NULL, employee_id INT DEFAULT NULL, category VARCHAR(255) NOT NULL, theory TINYINT(1) NOT NULL, theory_hours INT NOT NULL, course_hours INT NOT NULL, start_at DATE DEFAULT NULL, ended_at DATE DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_169E6FB99395C3F3 (customer_id), INDEX IDX_169E6FB98C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE course_schedule (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, course_id INT NOT NULL, start_at DATETIME NOT NULL, duration INT NOT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_F38C7CAD8C03F15C (employee_id), INDEX IDX_F38C7CAD591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE theory (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, start_at DATETIME NOT NULL, duration INT NOT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_F3908B388C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE theory_attendance_list (id INT AUTO_INCREMENT NOT NULL, theory_id INT NOT NULL, customer_id INT NOT NULL, INDEX IDX_5120A8BB6441A32F (theory_id), INDEX IDX_5120A8BB9395C3F3 (customer_id), UNIQUE INDEX UNIQ_USER (theory_id, customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE theory_attendance_list_course (theory_attendance_list_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_8AE04720F2FAC2B9 (theory_attendance_list_id), INDEX IDX_8AE04720591CC992 (course_id), PRIMARY KEY(theory_attendance_list_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, phone_number VARCHAR(255) NOT NULL, categories LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)', email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course ADD CONSTRAINT FK_169E6FB99395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course ADD CONSTRAINT FK_169E6FB98C03F15C FOREIGN KEY (employee_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_schedule ADD CONSTRAINT FK_F38C7CAD8C03F15C FOREIGN KEY (employee_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_schedule ADD CONSTRAINT FK_F38C7CAD591CC992 FOREIGN KEY (course_id) REFERENCES course (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory ADD CONSTRAINT FK_F3908B388C03F15C FOREIGN KEY (employee_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list ADD CONSTRAINT FK_5120A8BB6441A32F FOREIGN KEY (theory_id) REFERENCES theory (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list ADD CONSTRAINT FK_5120A8BB9395C3F3 FOREIGN KEY (customer_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list_course ADD CONSTRAINT FK_8AE04720F2FAC2B9 FOREIGN KEY (theory_attendance_list_id) REFERENCES theory_attendance_list (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list_course ADD CONSTRAINT FK_8AE04720591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE course DROP FOREIGN KEY FK_169E6FB99395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course DROP FOREIGN KEY FK_169E6FB98C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_schedule DROP FOREIGN KEY FK_F38C7CAD8C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE course_schedule DROP FOREIGN KEY FK_F38C7CAD591CC992
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory DROP FOREIGN KEY FK_F3908B388C03F15C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list DROP FOREIGN KEY FK_5120A8BB6441A32F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list DROP FOREIGN KEY FK_5120A8BB9395C3F3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list_course DROP FOREIGN KEY FK_8AE04720F2FAC2B9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE theory_attendance_list_course DROP FOREIGN KEY FK_8AE04720591CC992
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE car
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE course
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE course_schedule
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE faq
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE theory
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE theory_attendance_list
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE theory_attendance_list_course
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
