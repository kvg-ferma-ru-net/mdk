DROP DATABASE IF EXISTS `test_db_mdk`;
CREATE DATABASE `test_db_mdk`;

CREATE TABLE `test_db_mdk`.`orders` ( `id` INT UNSIGNED NOT NULL AUTO_INCREMENT , `items` TEXT NOT NULL , `customer` VARCHAR(255) NOT NULL , `notify` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB; 

INSERT INTO `test_db_mdk`.`orders` (`id`, `items`, `customer`, `notify`) VALUES (1, '[{\"name\":\"Брюки для катания\",\"price\":7500,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":6,\"amount\":7500}]', 'Test', 'box@domain.zone');

INSERT INTO `test_db_mdk`.`orders` (`id`, `items`, `customer`, `notify`) VALUES (2, '[{\"name\":\"Одеяло 1,5 спальное\",\"price\":2171,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":1,\"amount\":2171},{\"name\":\"Полуботинки женские1\",\"price\":1500,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":1,\"amount\":1500},{\"name\":\"Полуботинки женские 2\",\"price\":1500,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":1,\"amount\":1500},{\"name\":\"Кеды 1\",\"price\":1200,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":1,\"amount\":1200},{\"name\":\"Кеды 2\",\"price\":1200,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":1,\"amount\":1200}]', 'Test', 'box@domain.zone');

INSERT INTO `test_db_mdk`.`orders` (`id`, `items`, `customer`, `notify`) VALUES (3, '[{\"name\":\"Шкаф\",\"price\":2500,\"quantity\":1,\"paymentMethod\":2,\"type\":1,\"vat\":6,\"amount\":2500}]', 'Test', 'box@domain.zone');

INSERT INTO `test_db_mdk`.`orders` (`id`, `items`, `customer`, `notify`) VALUES (4, '[{\"name\":\"Barbaro Shave Set №1 - Подарочный набор для бритья\",\"price\":2500,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":6,\"amount\":2500},{\"name\":\"Доставка: Доставка до пункта выдачи (СДЭК) (5 дней)\",\"price\":350,\"quantity\":1,\"paymentMethod\":4,\"type\":4,\"vat\":6,\"amount\":350}]', 'Test', 'box@domain.zone');

INSERT INTO `test_db_mdk`.`orders` (`id`, `items`, `customer`, `notify`) VALUES (5, '[{\"name\":\"Сапоги детские\",\"price\":940.5,\"quantity\":1,\"paymentMethod\":4,\"type\":1,\"vat\":6,\"amount\":940.5}]', 'Test', 'box@domain.zone');

CREATE TABLE `test_db_mdk`.`receipts` ( `id` INT NOT NULL AUTO_INCREMENT , `subtype` TINYINT , `cashbox` VARCHAR(255) NOT NULL , `order_id` VARCHAR(255) NOT NULL , `site_id` VARCHAR(255) NOT NULL , `uuid` VARCHAR(32) NOT NULL , `status` TINYINT NOT NULL , `type` TINYINT NOT NULL , `items` TEXT NOT NULL , `taxation` TINYINT NOT NULL , `amount` TEXT NOT NULL , `customer` TEXT NOT NULL , `notify` TEXT NOT NULL , `location` VARCHAR(255) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB; 

ALTER TABLE `test_db_mdk`.`receipts` ADD INDEX `filter` (`order_id`, `type`, `subtype`, `status`); 

USE `test_db_mdk`;