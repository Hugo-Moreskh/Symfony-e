<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805095343 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45F6A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7234A45FA76ED395 ON friendship (user_id)');
        $this->addSql('CREATE INDEX IDX_7234A45F6A5458E8 ON friendship (friend_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FA76ED395');
        $this->addSql('ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45F6A5458E8');
        $this->addSql('DROP INDEX IDX_7234A45FA76ED395 ON friendship');
        $this->addSql('DROP INDEX IDX_7234A45F6A5458E8 ON friendship');
    }
}
