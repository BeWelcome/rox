<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260624120000 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add browser push subscriptions and notification queue';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE IF NOT EXISTS browser_push_subscription (
                id INT AUTO_INCREMENT NOT NULL,
                member_id INT NOT NULL,
                endpoint_hash CHAR(64) NOT NULL,
                endpoint LONGTEXT NOT NULL,
                public_key LONGTEXT NOT NULL,
                auth_token VARCHAR(255) NOT NULL,
                content_encoding VARCHAR(32) DEFAULT NULL,
                user_agent VARCHAR(255) DEFAULT NULL,
                last_seen DATETIME DEFAULT NULL,
                last_error VARCHAR(255) DEFAULT NULL,
                created DATETIME NOT NULL,
                updated DATETIME DEFAULT NULL,
                UNIQUE INDEX uniq_browser_push_subscription_endpoint_hash (endpoint_hash),
                INDEX idx_browser_push_subscription_member (member_id),
                CONSTRAINT fk_browser_push_subscription_member
                    FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS browser_push_notification (
                id INT AUTO_INCREMENT NOT NULL,
                member_id INT NOT NULL,
                status VARCHAR(32) NOT NULL,
                type VARCHAR(32) NOT NULL,
                sender_username VARCHAR(255) DEFAULT NULL,
                url VARCHAR(2048) NOT NULL,
                last_error VARCHAR(255) DEFAULT NULL,
                attempts INT DEFAULT 0 NOT NULL,
                created DATETIME NOT NULL,
                updated DATETIME DEFAULT NULL,
                INDEX idx_browser_push_notification_status_created (status, created, id),
                INDEX idx_browser_push_notification_member_status (member_id, status, id),
                CONSTRAINT fk_browser_push_notification_member
                    FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql('
            CREATE TABLE IF NOT EXISTS browser_push_notification_delivery (
                id INT AUTO_INCREMENT NOT NULL,
                notification_id INT NOT NULL,
                subscription_id INT DEFAULT NULL,
                status VARCHAR(32) NOT NULL,
                attempts INT DEFAULT 0 NOT NULL,
                last_error VARCHAR(255) DEFAULT NULL,
                created DATETIME NOT NULL,
                updated DATETIME DEFAULT NULL,
                INDEX idx_browser_push_notification_delivery_notification_status (notification_id, status),
                INDEX idx_browser_push_notification_delivery_subscription (subscription_id),
                CONSTRAINT fk_browser_push_notification_delivery_notification
                    FOREIGN KEY (notification_id) REFERENCES browser_push_notification (id) ON DELETE CASCADE,
                CONSTRAINT fk_browser_push_notification_delivery_subscription
                    FOREIGN KEY (subscription_id) REFERENCES browser_push_subscription (id) ON DELETE SET NULL,
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        $this->addSql("
            INSERT INTO preferences (
                position,
                codeName,
                codeDescription,
                Description,
                created,
                DefaultValue,
                PossibleValues,
                Status
            ) VALUES (
                56,
                'PreferenceBrowserNotifications',
                'BrowserNotificationsDesc',
                'This preference stores if the member wants browser push notifications.',
                CURRENT_TIMESTAMP,
                'No',
                'No;OpenOnly;Always',
                'Normal'
            )
        ");
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql("
            DELETE mp
            FROM memberspreferences mp
            INNER JOIN preferences p ON p.id = mp.IdPreference
            WHERE p.codeName = 'PreferenceBrowserNotifications'
        ");
        $this->addSql("DELETE FROM preferences WHERE codeName = 'PreferenceBrowserNotifications'");
        $this->addSql('DROP TABLE browser_push_notification_delivery');
        $this->addSql('DROP TABLE browser_push_notification');
        $this->addSql('DROP TABLE browser_push_subscription');
    }
}
