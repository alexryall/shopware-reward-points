<?php declare(strict_types=1);

namespace AlexRyall\RewardPoints\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1584189007Transaction extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1584189007;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `ar_rewardpoints_transaction` (
                `id` BINARY(16) NOT NULL,
                `action` INT(11) NOT NULL,
                `points` INT(11) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                `order_id` BINARY(16) NULL,
                `order_version_id` BINARY(16) NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                KEY `fk.ar_rewardpoints_transaction.customer_id` (`customer_id`),
                KEY `fk.ar_rewardpoints_transaction.order_id` (`order_id`,`order_version_id`),
                CONSTRAINT `fk.ar_rewardpoints_transaction.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.ar_rewardpoints_transaction.order_id` FOREIGN KEY (`order_id`,`order_version_id`) REFERENCES `order` (`id`,`version_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
