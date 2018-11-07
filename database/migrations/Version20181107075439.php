<?php

namespace Database\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema as Schema;

class Version20181107075439 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE mail_chimp_lists (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', campaign_defaults LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', contact LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', email_type_option TINYINT(1) NOT NULL, mail_chimp_id VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, notify_on_subscribe VARCHAR(255) DEFAULT NULL, notify_on_unsubscribe VARCHAR(255) DEFAULT NULL, permission_reminder VARCHAR(255) NOT NULL, use_archive_bar TINYINT(1) DEFAULT NULL, visibility VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mail_chimp_list_members (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', list_id VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, unique_email_id VARCHAR(255) DEFAULT NULL, email_type VARCHAR(255) DEFAULT NULL, status VARCHAR(255) NOT NULL, merge_fields LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', language VARCHAR(255) DEFAULT NULL, vip TINYINT(1) DEFAULT NULL, location LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', marketing_permissions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', mail_chimp_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE mail_chimp_lists');
        $this->addSql('DROP TABLE mail_chimp_list_members');
    }
}
