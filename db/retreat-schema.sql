/* https://dbdiagram.io/d/6445306c6b319470510c0fb2 */

DROP DATABASE IF EXISTS retreat;

CREATE DATABASE retreat;

USE retreat;

-- ---------------------------------------------------------------------
-- Create tables and FKs
-- ---------------------------------------------------------------------

CREATE TABLE `scenario` (
  `scenario_id` integer PRIMARY KEY AUTO_INCREMENT,
  `scenario_name` varchar(255),
  `scenario_descr` varchar(255),
  `account_type_id` integer,
  `scenario_parent_id` integer,
  `created_at` timestamp DEFAULT (now()),
  `modified_at` timestamp DEFAULT (now())
);

CREATE TABLE `account_type` (
  `account_type_id` integer PRIMARY KEY,
  `account_type_descr` varchar(255),
  `created_at` timestamp DEFAULT (now()),
  `modified_at` timestamp DEFAULT (now())
);

CREATE TABLE `expense` (
  `expense_id` integer PRIMARY KEY AUTO_INCREMENT,
  `scenario_id` integer,
  `expense_name` varchar(255),
  `expense_descr` varchar(255),
  `amount` DECIMAL(13, 2),
  `inflation_rate` DECIMAL(5, 3),
  `begin_year` integer,
  `begin_month` integer,
  `end_year` integer,
  `end_month` integer,
  `repeat_every` integer,
  `created_at` timestamp DEFAULT (now()),
  `modified_at` timestamp DEFAULT (now())
);

CREATE TABLE `asset` (
  `asset_id` integer PRIMARY KEY AUTO_INCREMENT,
  `scenario_id` integer,
  `asset_name` varchar(255),
  `asset_descr` varchar(255),
  `opening_balance` DECIMAL(13, 2),
  `max_withdrawal` DECIMAL(13, 2),
  `apr` DECIMAL(5, 3),
  `begin_after` integer,
  `begin_year` integer,
  `begin_month` integer,
  `created_at` timestamp DEFAULT (now()),
  `modified_at` timestamp DEFAULT (now())
);

ALTER TABLE `scenario` ADD FOREIGN KEY (`account_type_id`) REFERENCES `account_type` (`account_type_id`);

ALTER TABLE `expense` ADD FOREIGN KEY (`scenario_id`) REFERENCES `scenario` (`scenario_id`);

ALTER TABLE `asset` ADD FOREIGN KEY (`scenario_id`) REFERENCES `scenario` (`scenario_id`);
