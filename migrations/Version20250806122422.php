<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250806122422 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96E92F8F78');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D');
        $this->addSql('DROP INDEX IDX_DB021E96F624B39D ON messages');
        $this->addSql('DROP INDEX IDX_DB021E96E92F8F78 ON messages');
        $this->addSql('ALTER TABLE messages CHANGE group_id group_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE messages CHANGE group_id group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96E92F8F78 FOREIGN KEY (recipient_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_DB021E96F624B39D ON messages (sender_id)');
        $this->addSql('CREATE INDEX IDX_DB021E96E92F8F78 ON messages (recipient_id)');
    }
}
