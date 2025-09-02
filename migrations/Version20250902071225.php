<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250902071225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE grupo (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, estado VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios_grupo (usuarios_id INT NOT NULL, grupo_id INT NOT NULL, INDEX IDX_F439C7AEF01D3B25 (usuarios_id), INDEX IDX_F439C7AE9C833003 (grupo_id), PRIMARY KEY(usuarios_id, grupo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE usuarios_grupo ADD CONSTRAINT FK_F439C7AEF01D3B25 FOREIGN KEY (usuarios_id) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuarios_grupo ADD CONSTRAINT FK_F439C7AE9C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE usuarios ADD rol VARCHAR(150) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuarios_grupo DROP FOREIGN KEY FK_F439C7AEF01D3B25');
        $this->addSql('ALTER TABLE usuarios_grupo DROP FOREIGN KEY FK_F439C7AE9C833003');
        $this->addSql('DROP TABLE grupo');
        $this->addSql('DROP TABLE usuarios_grupo');
        $this->addSql('ALTER TABLE usuarios DROP rol');
    }
}
