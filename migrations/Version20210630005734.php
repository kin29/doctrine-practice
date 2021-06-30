<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210630005734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tomato DROP FOREIGN KEY FK_2C14401AD41D1D42');
        $this->addSql('ALTER TABLE tomato ADD CONSTRAINT FK_2C14401AD41D1D42 FOREIGN KEY (pizza_id) REFERENCES delicious_pizza (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tomato DROP FOREIGN KEY FK_2C14401AD41D1D42');
        $this->addSql('ALTER TABLE tomato ADD CONSTRAINT FK_2C14401AD41D1D42 FOREIGN KEY (pizza_id) REFERENCES delicious_pizza (id)');
    }
}
