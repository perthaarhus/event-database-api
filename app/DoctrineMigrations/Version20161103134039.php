<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161103134039 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE entity_property (entity_type VARCHAR(255) NOT NULL, entity_id INT NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(entity_type, entity_id, name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA751A5BC03');
        $this->addSql('DROP INDEX IDX_3BAE0AA751A5BC03 ON event');
        $this->addSql('ALTER TABLE event DROP feed_id, DROP feed_event_id');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE entity_property');
        $this->addSql('ALTER TABLE event ADD feed_id INT DEFAULT NULL, ADD feed_event_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA751A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA751A5BC03 ON event (feed_id)');
    }
}
