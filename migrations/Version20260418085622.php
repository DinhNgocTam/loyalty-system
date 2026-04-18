<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260418085622 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE gift (id INT AUTO_INCREMENT NOT NULL, gift_name VARCHAR(150) NOT NULL, point_cost INT NOT NULL, stock INT NOT NULL, status VARCHAR(20) NOT NULL, INDEX idx_gift_status_stock (status, stock), INDEX idx_gift_point_cost (point_cost), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE member (id INT AUTO_INCREMENT NOT NULL, fullname VARCHAR(150) NOT NULL, email VARCHAR(180) NOT NULL, created_at DATETIME NOT NULL, INDEX idx_member_created_at (created_at), UNIQUE INDEX uniq_member_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE point (id INT AUTO_INCREMENT NOT NULL, point_amount INT NOT NULL, description VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, wallet_id INT NOT NULL, transaction_id INT DEFAULT NULL, redemption_id INT DEFAULT NULL, INDEX IDX_B7A5F324712520F3 (wallet_id), INDEX idx_point_wallet_created (wallet_id, created_at), INDEX idx_point_transaction_id (transaction_id), INDEX idx_point_redemption_id (redemption_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE redemption (id INT AUTO_INCREMENT NOT NULL, points_used INT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, member_id INT NOT NULL, gift_id INT NOT NULL, INDEX IDX_2C6134237597D3FE (member_id), INDEX idx_redemption_member_status_created (member_id, status, created_at), INDEX idx_redemption_gift_id (gift_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, amount NUMERIC(15, 2) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, member_id INT NOT NULL, INDEX IDX_EAA81A4C7597D3FE (member_id), INDEX idx_tx_member_status_created (member_id, status, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wallet (id INT AUTO_INCREMENT NOT NULL, balance INT DEFAULT 0 NOT NULL, updated_at DATETIME NOT NULL, member_id INT NOT NULL, UNIQUE INDEX UNIQ_7C68921F7597D3FE (member_id), INDEX idx_wallet_updated_at (updated_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F3242FC0CB0F FOREIGN KEY (transaction_id) REFERENCES transactions (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE point ADD CONSTRAINT FK_B7A5F324DDD59F9C FOREIGN KEY (redemption_id) REFERENCES redemption (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE redemption ADD CONSTRAINT FK_2C6134237597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE redemption ADD CONSTRAINT FK_2C61342397A95A83 FOREIGN KEY (gift_id) REFERENCES gift (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921F7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324712520F3');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F3242FC0CB0F');
        $this->addSql('ALTER TABLE point DROP FOREIGN KEY FK_B7A5F324DDD59F9C');
        $this->addSql('ALTER TABLE redemption DROP FOREIGN KEY FK_2C6134237597D3FE');
        $this->addSql('ALTER TABLE redemption DROP FOREIGN KEY FK_2C61342397A95A83');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C7597D3FE');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921F7597D3FE');
        $this->addSql('DROP TABLE gift');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE point');
        $this->addSql('DROP TABLE redemption');
        $this->addSql('DROP TABLE transactions');
        $this->addSql('DROP TABLE wallet');
    }
}
