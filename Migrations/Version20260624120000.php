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
        if (!$this->browserPushSubscriptionTableExists()) {
            $this->addSql('
                CREATE TABLE browser_push_subscription (
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
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE browser_push_subscription
                    ADD CONSTRAINT fk_browser_push_subscription_member
                    FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE
            ');
        }

        if (!$this->browserPushNotificationTableExists()) {
            $this->addSql('
                CREATE TABLE browser_push_notification (
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
                    INDEX idx_browser_push_notification_status (status),
                    INDEX idx_browser_push_notification_member (member_id),
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE browser_push_notification
                    ADD CONSTRAINT fk_browser_push_notification_member
                    FOREIGN KEY (member_id) REFERENCES member (id) ON DELETE CASCADE
            ');
        } elseif (!$this->browserPushNotificationColumnExists('attempts')) {
            $this->addSql('ALTER TABLE browser_push_notification ADD attempts INT DEFAULT 0 NOT NULL');
        }

        if (!$this->browserPushNotificationDeliveryTableExists()) {
            $this->addSql('
                CREATE TABLE browser_push_notification_delivery (
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
                    PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            ');
            $this->addSql('
                ALTER TABLE browser_push_notification_delivery
                    ADD CONSTRAINT fk_browser_push_notification_delivery_notification
                    FOREIGN KEY (notification_id) REFERENCES browser_push_notification (id) ON DELETE CASCADE
            ');
            $this->addSql('
                ALTER TABLE browser_push_notification_delivery
                    ADD CONSTRAINT fk_browser_push_notification_delivery_subscription
                    FOREIGN KEY (subscription_id) REFERENCES browser_push_subscription (id) ON DELETE SET NULL
            ');
        }

        if (!$this->browserPushPreferenceExists()) {
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
                    'Yes',
                    'Yes;No',
                    'Normal'
                )
            ");
        }
    }

    #[Override]
    public function down(Schema $schema): void
    {
        if ($this->browserPushNotificationDeliveryTableExists()) {
            if ($this->browserPushNotificationDeliveryNotificationForeignKeyExists()) {
                $this->addSql('
                    ALTER TABLE browser_push_notification_delivery
                    DROP FOREIGN KEY fk_browser_push_notification_delivery_notification
                ');
            }
            if ($this->browserPushNotificationDeliverySubscriptionForeignKeyExists()) {
                $this->addSql('
                    ALTER TABLE browser_push_notification_delivery
                    DROP FOREIGN KEY fk_browser_push_notification_delivery_subscription
                ');
            }
            $this->addSql('DROP TABLE browser_push_notification_delivery');
        }

        if ($this->browserPushPreferenceExists()) {
            $this->addSql("DELETE FROM preferences WHERE codeName = 'PreferenceBrowserNotifications'");
        }

        if ($this->browserPushNotificationTableExists()) {
            if ($this->browserPushNotificationForeignKeyExists()) {
                $this->addSql('
                    ALTER TABLE browser_push_notification
                    DROP FOREIGN KEY fk_browser_push_notification_member
                ');
            }
            $this->addSql('DROP TABLE browser_push_notification');
        }

        if (!$this->browserPushSubscriptionTableExists()) {
            return;
        }

        if ($this->browserPushSubscriptionForeignKeyExists()) {
            $this->addSql('ALTER TABLE browser_push_subscription DROP FOREIGN KEY fk_browser_push_subscription_member');
        }
        $this->addSql('DROP TABLE browser_push_subscription');
    }

    private function browserPushSubscriptionTableExists(): bool
    {
        return $this->tableExists('browser_push_subscription');
    }

    private function browserPushNotificationTableExists(): bool
    {
        return $this->tableExists('browser_push_notification');
    }

    private function browserPushNotificationDeliveryTableExists(): bool
    {
        return $this->tableExists('browser_push_notification_delivery');
    }

    private function browserPushSubscriptionForeignKeyExists(): bool
    {
        return $this->foreignKeyExists('browser_push_subscription', 'fk_browser_push_subscription_member');
    }

    private function browserPushNotificationForeignKeyExists(): bool
    {
        return $this->foreignKeyExists('browser_push_notification', 'fk_browser_push_notification_member');
    }

    private function browserPushNotificationDeliveryNotificationForeignKeyExists(): bool
    {
        return $this->foreignKeyExists(
            'browser_push_notification_delivery',
            'fk_browser_push_notification_delivery_notification'
        );
    }

    private function browserPushNotificationDeliverySubscriptionForeignKeyExists(): bool
    {
        return $this->foreignKeyExists(
            'browser_push_notification_delivery',
            'fk_browser_push_notification_delivery_subscription'
        );
    }

    private function browserPushPreferenceExists(): bool
    {
        return 0 < (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM preferences WHERE codeName = ?',
            ['PreferenceBrowserNotifications']
        );
    }

    private function browserPushNotificationColumnExists(string $columnName): bool
    {
        return $this->columnExists('browser_push_notification', $columnName);
    }

    private function tableExists(string $tableName): bool
    {
        return 0 < (int) $this->connection->fetchOne('
            SELECT COUNT(*)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
        ', [$tableName]);
    }

    private function foreignKeyExists(string $tableName, string $foreignKeyName): bool
    {
        return 0 < (int) $this->connection->fetchOne("
            SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$tableName, $foreignKeyName]);
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        return 0 < (int) $this->connection->fetchOne('
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ', [$tableName, $columnName]);
    }
}
