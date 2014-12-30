<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141230140956 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Contributor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5CF318445E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE packages_contributors (contributor_id INT NOT NULL, package_id INT NOT NULL, INDEX IDX_1DD11BE7A19A357 (contributor_id), INDEX IDX_1DD11BEF44CABFF (package_id), PRIMARY KEY(contributor_id, package_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Package (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_11D55E095E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE packages_contributors ADD CONSTRAINT FK_1DD11BE7A19A357 FOREIGN KEY (contributor_id) REFERENCES Contributor (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE packages_contributors ADD CONSTRAINT FK_1DD11BEF44CABFF FOREIGN KEY (package_id) REFERENCES Package (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE packages_contributors DROP FOREIGN KEY FK_1DD11BE7A19A357');
        $this->addSql('ALTER TABLE packages_contributors DROP FOREIGN KEY FK_1DD11BEF44CABFF');
        $this->addSql('DROP TABLE Contributor');
        $this->addSql('DROP TABLE packages_contributors');
        $this->addSql('DROP TABLE Package');
    }
}
