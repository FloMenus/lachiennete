<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705173437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add article.sold_at & purchase.delivery_address';
    }
    

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD sold_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase ADD delivery_address VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP sold_at');
        $this->addSql('ALTER TABLE purchase DROP delivery_address');
    }
}
