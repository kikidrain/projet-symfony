<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829123821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto_transaction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, crypto_id INTEGER NOT NULL, admin_user_id INTEGER DEFAULT NULL, type VARCHAR(20) NOT NULL, amount NUMERIC(20, 8) NOT NULL, price_at_transaction NUMERIC(20, 8) NOT NULL, total_value NUMERIC(20, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description CLOB DEFAULT NULL, is_visible BOOLEAN NOT NULL, CONSTRAINT FK_5380A1D5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5380A1D5E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5380A1D56352511C FOREIGN KEY (admin_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5380A1D5A76ED395 ON crypto_transaction (user_id)');
        $this->addSql('CREATE INDEX IDX_5380A1D5E9571A63 ON crypto_transaction (crypto_id)');
        $this->addSql('CREATE INDEX IDX_5380A1D56352511C ON crypto_transaction (admin_user_id)');
        $this->addSql('DROP TABLE "transaction"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "transaction" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, crypto_id INTEGER NOT NULL, admin_user_id INTEGER DEFAULT NULL, type VARCHAR(20) NOT NULL COLLATE "BINARY", amount NUMERIC(20, 8) NOT NULL, price_at_transaction NUMERIC(20, 8) NOT NULL, total_value NUMERIC(20, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description CLOB DEFAULT NULL COLLATE "BINARY", is_visible BOOLEAN NOT NULL, CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D16352511C FOREIGN KEY (admin_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_723705D16352511C ON "transaction" (admin_user_id)');
        $this->addSql('CREATE INDEX IDX_723705D1E9571A63 ON "transaction" (crypto_id)');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON "transaction" (user_id)');
        $this->addSql('DROP TABLE crypto_transaction');
    }
}
