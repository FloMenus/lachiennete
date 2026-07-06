<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705180948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Stock par article : quantité (défaut 1), les articles déjà vendus passent à 0';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD quantity INT DEFAULT 1 NOT NULL');
        $this->addSql('UPDATE article SET quantity = 0 WHERE sold_at IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP quantity');
    }
}
