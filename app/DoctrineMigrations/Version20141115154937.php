<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Make checked in a timestamp.
 */
class Version20141115154937 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE ticket CHANGE checked_in checked_in DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE ticket CHANGE checked_in checked_in TINYINT(1) NOT NULL');
    }
}
