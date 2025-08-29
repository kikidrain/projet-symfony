<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829123334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, symbol VARCHAR(10) NOT NULL, current_price NUMERIC(20, 8) NOT NULL, last_updated DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description VARCHAR(500) DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE crypto_price_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, crypto_id INTEGER NOT NULL, price NUMERIC(20, 8) NOT NULL, recorded_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_F94B3B55E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F94B3B55E9571A63 ON crypto_price_history (crypto_id)');
        $this->addSql('CREATE TABLE "transaction" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, crypto_id INTEGER NOT NULL, admin_user_id INTEGER DEFAULT NULL, type VARCHAR(20) NOT NULL, amount NUMERIC(20, 8) NOT NULL, price_at_transaction NUMERIC(20, 8) NOT NULL, total_value NUMERIC(20, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , description CLOB DEFAULT NULL, is_visible BOOLEAN NOT NULL, CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D1E9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_723705D16352511C FOREIGN KEY (admin_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON "transaction" (user_id)');
        $this->addSql('CREATE INDEX IDX_723705D1E9571A63 ON "transaction" (crypto_id)');
        $this->addSql('CREATE INDEX IDX_723705D16352511C ON "transaction" (admin_user_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, username VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , is_active BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE TABLE wallet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, crypto_id INTEGER NOT NULL, balance NUMERIC(20, 8) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , updated_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_7C68921FE9571A63 FOREIGN KEY (crypto_id) REFERENCES crypto (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7C68921FA76ED395 ON wallet (user_id)');
        $this->addSql('CREATE INDEX IDX_7C68921FE9571A63 ON wallet (crypto_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE crypto');
        $this->addSql('DROP TABLE crypto_price_history');
        $this->addSql('DROP TABLE "transaction"');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE wallet');
    }
}
