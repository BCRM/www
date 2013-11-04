<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20131104210154 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE ticket CHANGE email email VARCHAR(255) NOT NULL");
        $this->addSql("CREATE INDEX email_idx ON ticket (email)");
        $this->addSql("ALTER TABLE unregistration CHANGE email email VARCHAR(255) NOT NULL");
        $this->addSql("CREATE INDEX email_idx ON unregistration (email)");
        $this->addSql("ALTER TABLE registration CHANGE email email VARCHAR(255) NOT NULL");
        $this->addSql("CREATE INDEX email_idx ON registration (email)");
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("DROP INDEX email_idx ON registration");
        $this->addSql("ALTER TABLE registration CHANGE email email LONGTEXT NOT NULL");
        $this->addSql("DROP INDEX email_idx ON ticket");
        $this->addSql("ALTER TABLE ticket CHANGE email email LONGTEXT NOT NULL");
        $this->addSql("DROP INDEX email_idx ON unregistration");
        $this->addSql("ALTER TABLE unregistration CHANGE email email LONGTEXT NOT NULL");
    }
}
