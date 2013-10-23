<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use BCRM\BackendBundle\Entity\Event\Registration;

class Version20131023124611 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE ticket ADD type INT NOT NULL AFTER event_id");
        $this->addSql("ALTER TABLE registration ADD type INT NOT NULL AFTER event_id");
        $this->addSql(sprintf("UPDATE ticket SET type = %d", Registration::TYPE_NORMAL));
        $this->addSql(sprintf("UPDATE registration SET type = %d", Registration::TYPE_NORMAL));
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE ticket DROP type");
        $this->addSql("ALTER TABLE registration DROP type");
    }
}
