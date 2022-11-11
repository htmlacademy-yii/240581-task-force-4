CREATE DATABASE `tf_db`
    DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE `tf_db`;

CREATE TABLE `category` (
    `category_id` INT PRIMARY KEY AUTO_INCREMENT,
    `category_name` VARCHAR(30) NOT NULL,
    `icon` VARCHAR(10) NOT NULL
);

CREATE TABLE `city` (
    `city_id` INT PRIMARY KEY AUTO_INCREMENT,
    `city_name` VARCHAR(50) NOT NULL,
    `city_latitude` VARCHAR(255),
    `city_longitude` VARCHAR(255)
);

CREATE TABLE `user` (
    `user_id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` CHAR(255) NOT NULL,
    `avatar` VARCHAR(255) UNIQUE,
    `date_add` DATETIME NOT NULL DEFAULT NOW(),
    `city_id` INT,
    `phone` VARCHAR(11) UNIQUE DEFAULT NULL,
    `telegram` VARCHAR(64) UNIQUE DEFAULT NULL,
    `personal_information` TEXT,
    `rating` INT UNSIGNED,
    `status` VARCHAR(10) NOT NULL DEFAULT 'free',
    `birthday` DATE,
    /* Столбец "user_role" удалён в миграции m221106_141708_drop_column_user_role_from_user_table */
    `user_role` VARCHAR(45) DEFAULT NULL,
    `show_contacts` INT(10) DEFAULT NULL,
    FOREIGN KEY (`city_id`) REFERENCES city (`city_id`)
);



CREATE TABLE `user_category` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES user (`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`) ON DELETE CASCADE,
    UNIQUE KEY `relation_row_unique` (`category_id`, `user_id`)
);



CREATE TABLE `task` (
    `task_id` INT PRIMARY KEY AUTO_INCREMENT,
    `customer_id` INT NOT NULL,
    `executor_id` INT,
    `category_id` INT NOT NULL,
    `city_id` INT,
    `task_name` VARCHAR(50) NOT NULL,
    `task_essence` VARCHAR(80) NOT NULL,
    `task_details` TEXT NOT NULL,
    `task_budget` INT UNSIGNED,
    `task_longitude` VARCHAR(255),
    `task_latitude` VARCHAR(255),
    `task_date_create` DATETIME NOT NULL,
    `task_status` VARCHAR(10),
    `task_deadline` DATETIME,
    `grade` INT,
    `review` VARCHAR(255),
    `date_completion` DATETIME,
    FOREIGN KEY (`customer_id`) REFERENCES `user` (`user_id`),
    FOREIGN KEY (`executor_id`) REFERENCES `user` (`user_id`),
    FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`),
    FOREIGN KEY (`city_id`) REFERENCES `city` (`city_id`)
);

CREATE TABLE `file` (
    `file_id` INT PRIMARY KEY AUTO_INCREMENT,
    `task_id` INT,
    `task_file_name` VARCHAR(255) NOT NULL UNIQUE,
    `task_file_base_name` VARCHAR(255) NOT NULL,
    FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`)
);

CREATE TABLE `respond` (
    `respond_id` INT PRIMARY KEY AUTO_INCREMENT,
    `executor_id` INT NOT NULL,
    `task_id` INT NOT NULL,
    `accepted` VARCHAR(10) DEFAULT NULL,
    `challenger_price` INT UNSIGNED,
    `date_add` DATETIME NOT NULL DEFAULT NOW(),
    `promising_message` VARCHAR(255),
    FOREIGN KEY (`executor_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`task_id`) REFERENCES `task` (`task_id`) ON DELETE CASCADE,
    UNIQUE KEY `relation_row_unique` (`task_id`, `executor_id`)
);
