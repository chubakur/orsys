CREATE DATABASE orsys_orders CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE orsys_orders;

CREATE TABLE orders (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `client` INT NOT NULL,
  `description` TEXT NOT NULL,
  `cost` INT NOT NULL,
  `performer` INT DEFAULT NULL,
  `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);