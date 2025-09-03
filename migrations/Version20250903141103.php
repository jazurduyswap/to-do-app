<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903141103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB259EEE5107');
        $this->addSql('DROP INDEX IDX_527EDB259EEE5107 ON task');
        $this->addSql('ALTER TABLE task ADD usuario_id INT DEFAULT NULL, CHANGE padre_task_id parent_task_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25FFFE75C0 FOREIGN KEY (parent_task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25DB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_527EDB25FFFE75C0 ON task (parent_task_id)');
        $this->addSql('CREATE INDEX IDX_527EDB25DB38439E ON task (usuario_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25FFFE75C0');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25DB38439E');
        $this->addSql('DROP INDEX IDX_527EDB25FFFE75C0 ON task');
        $this->addSql('DROP INDEX IDX_527EDB25DB38439E ON task');
        $this->addSql('ALTER TABLE task ADD padre_task_id INT DEFAULT NULL, DROP parent_task_id, DROP usuario_id');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB259EEE5107 FOREIGN KEY (padre_task_id) REFERENCES task (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_527EDB259EEE5107 ON task (padre_task_id)');
    }
}
