<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260706091908 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add app_user.currency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user ADD currency VARCHAR(3) DEFAULT \'EUR\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_user DROP currency');
    }
}
