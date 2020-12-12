<?php

declare(strict_types=1);

/*
 * This file is part of the "YAML Field for Symphony CMS" repository.
 *
 * Copyright 2020 Alannah Kearney <hi@alannahkearney.com>
 *
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 */

if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    throw new Exception(sprintf('Could not find composer autoload file %s. Did you run `composer update` in %s?', __DIR__.'/vendor/autoload.php', __DIR__));
}

require_once __DIR__.'/vendor/autoload.php';

use pointybeard\Symphony\Extended;

// Check if the class already exists before declaring it again.
if (false == class_exists('\\extension_yamlfield')) {
    final class extension_yamlfield extends Extended\AbstractExtension
    {
        private $tableName = 'tbl_fields_yaml';

        public function uninstall()
        {
            parent::uninstall();

            return Symphony::Database()->query("DROP TABLE IF EXISTS `{$this->tableName}`");
        }

        public function install()
        {
            parent::install();

            return Symphony::Database()->query(
                "CREATE TABLE `{$this->tableName}` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `field_id` int(11) unsigned NOT NULL,
                    `size` int(3) unsigned NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `field_id` (`field_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            );
        }
    }
}
