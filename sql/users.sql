CREATE DATABASE orsys_users CHARACTER SET utf8 COLLATE utf8_general_ci;

USE orsys_users;
CREATE TABLE users (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(32) NOT NULL,
  `role` VARCHAR(10) NOT NULL,
  `bill` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE(email)
);