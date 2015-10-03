CREATE DATABASE orsys_events CHARACTER SET utf8 COLLATE utf8_general_ci;
USE orsys_events;

CREATE TABLE events (
  `id` INT NOT NULL AUTO_INCREMENT,
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `event` TEXT NOT NULL,
  INDEX(`date`),
  PRIMARY KEY (`id`)
) ;