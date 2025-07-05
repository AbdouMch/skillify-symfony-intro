<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250705033801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add attendee and event tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE attendee (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE attendee_event (attendee_id INT NOT NULL, event_id INT NOT NULL, INDEX IDX_339BEF15BCFD782A (attendee_id), INDEX IDX_339BEF1571F7E88B (event_id), PRIMARY KEY(attendee_id, event_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATETIME NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attendee_event ADD CONSTRAINT FK_339BEF15BCFD782A FOREIGN KEY (attendee_id) REFERENCES attendee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE attendee_event ADD CONSTRAINT FK_339BEF1571F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE attendee_event DROP FOREIGN KEY FK_339BEF15BCFD782A');
        $this->addSql('ALTER TABLE attendee_event DROP FOREIGN KEY FK_339BEF1571F7E88B');
        $this->addSql('DROP TABLE attendee');
        $this->addSql('DROP TABLE attendee_event');
        $this->addSql('DROP TABLE event');
    }
}
