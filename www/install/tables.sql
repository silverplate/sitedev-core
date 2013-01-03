DROP TABLE IF EXISTS `~db prefix~back_log`;
DROP TABLE IF EXISTS `~db prefix~back_user_has_section`;
DROP TABLE IF EXISTS `~db prefix~back_user`;
DROP TABLE IF EXISTS `~db prefix~back_section`;

DROP TABLE IF EXISTS `~db prefix~front_document_has_navigation`;
DROP TABLE IF EXISTS `~db prefix~front_navigation`;
DROP TABLE IF EXISTS `~db prefix~front_data`;
DROP TABLE IF EXISTS `~db prefix~front_data_content_type`;
DROP TABLE IF EXISTS `~db prefix~front_document`;
DROP TABLE IF EXISTS `~db prefix~front_template`;
DROP TABLE IF EXISTS `~db prefix~front_controller`;

DROP TABLE IF EXISTS `~db prefix~session_param`;
DROP TABLE IF EXISTS `~db prefix~session`;
DROP TABLE IF EXISTS `~db prefix~user`;
DROP TABLE IF EXISTS `~db prefix~file_cache`;

CREATE TABLE IF NOT EXISTS `~db prefix~back_user` (
    `~db prefix~back_user_id` CHAR(10) NOT NULL,
    `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `login` VARCHAR(255) NOT NULL,
    `passwd` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NULL,
    `ip_restriction` TEXT NULL,
    `reminder_key` CHAR(30) NULL,
    `reminder_date` DATETIME NULL,
    PRIMARY KEY (`~db prefix~back_user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~back_section` (
    `~db prefix~back_section_id` CHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `uri` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NULL,
    PRIMARY KEY (`~db prefix~back_section_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~back_log` (
    `~db prefix~back_log_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `~db prefix~back_user_id` CHAR(10) NULL,
    `~db prefix~back_section_id` CHAR(10) NULL,
    `creation_date` DATETIME NOT NULL,
    `action_id` SMALLINT UNSIGNED NOT NULL,
    `entry_id` CHAR(30) NULL,
    `section_name` VARCHAR(255) NULL,
    `script_name` VARCHAR(255) NULL,
    `user_name` VARCHAR(255) NULL,
    `user_ip` CHAR(15) NULL,
    `user_agent` VARCHAR(255) NULL,
    `request_uri` TEXT NULL,
    `request_get` TEXT NULL,
    `request_post` TEXT NULL,
    `cookies` TEXT NULL,
    `description` TEXT NULL,
    PRIMARY KEY (`~db prefix~back_log_id`),
    INDEX `fk_~db prefix~back_log_~db prefix~back_user_id_idx` (`~db prefix~back_user_id` ASC),
    INDEX `fk_~db prefix~back_log_~db prefix~back_section_id_idx` (`~db prefix~back_section_id` ASC),
    CONSTRAINT `fk_~db prefix~back_log_~db prefix~back_user_id`
        FOREIGN KEY (`~db prefix~back_user_id`)
        REFERENCES `~db prefix~back_user` (`~db prefix~back_user_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~back_log_~db prefix~back_section_id`
        FOREIGN KEY (`~db prefix~back_section_id`)
        REFERENCES `~db prefix~back_section` (`~db prefix~back_section_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_template` (
    `~db prefix~front_template_id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `is_document_main` TINYINT(1) NOT NULL DEFAULT 0,
    `is_multiple` TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`~db prefix~front_template_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_controller` (
    `~db prefix~front_controller_id` CHAR(10) NOT NULL,
    `type_id` SMALLINT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `is_document_main` TINYINT(1) NOT NULL DEFAULT 0,
    `is_multiple` TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`~db prefix~front_controller_id`),
    UNIQUE INDEX `~db prefix~front_controller_type_filename_unq` (`filename` ASC, `type_id` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_document` (
    `~db prefix~front_document_id` CHAR(30) NOT NULL,
    `parent_id` CHAR(30) NULL,
    `~db prefix~front_controller_id` CHAR(10) NULL,
    `~db prefix~front_template_id` SMALLINT UNSIGNED NULL,
    `auth_status_id` SMALLINT UNSIGNED NULL,
    `title` VARCHAR(255) NOT NULL,
    `title_compact` VARCHAR(255) NULL,
    `folder` VARCHAR(255) NOT NULL,
    `link` VARCHAR(255) NULL,
    `uri` VARCHAR(255) NULL,
    `is_published` TINYINT(1) NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`~db prefix~front_document_id`),
    INDEX `fk_~db prefix~front_document_~db prefix~front_template_id_idx` (`~db prefix~front_template_id` ASC),
    INDEX `fk_~db prefix~front_document_~db prefix~front_controller_id_idx` (`~db prefix~front_controller_id` ASC),
    INDEX `fk_~db prefix~front_document_parent_id_idx` (`parent_id` ASC),
    UNIQUE INDEX `~db prefix~front_document_uri_unq` (`uri` ASC),
    CONSTRAINT `fk_~db prefix~front_document_~db prefix~front_template_id`
        FOREIGN KEY (`~db prefix~front_template_id`)
        REFERENCES `~db prefix~front_template` (`~db prefix~front_template_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~front_document_~db prefix~front_controller_id`
        FOREIGN KEY (`~db prefix~front_controller_id`)
        REFERENCES `~db prefix~front_controller` (`~db prefix~front_controller_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~front_document_parent_id`
        FOREIGN KEY (`parent_id`)
        REFERENCES `~db prefix~front_document` (`~db prefix~front_document_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_data_content_type` (
    `~db prefix~front_data_content_type_id` CHAR(10) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`~db prefix~front_data_content_type_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_data` (
    `~db prefix~front_data_id` CHAR(30) NOT NULL,
    `~db prefix~front_document_id` CHAR(30) NOT NULL,
    `~db prefix~front_controller_id` CHAR(10) NULL,
    `~db prefix~front_data_content_type_id` CHAR(10) NULL,
    `auth_status_id` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `tag` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NULL,
    `apply_type_id` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `is_mount` TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`~db prefix~front_data_id`),
    INDEX `fk_~db prefix~front_data_~db prefix~front_document_id_idx` (`~db prefix~front_document_id` ASC),
    INDEX `fk_~db prefix~front_data_~db prefix~front_data_content_type_id_idx` (`~db prefix~front_data_content_type_id` ASC),
    INDEX `fk_~db prefix~front_data_~db prefix~front_controller_id_idx` (`~db prefix~front_controller_id` ASC),
    CONSTRAINT `fk_~db prefix~front_data_~db prefix~front_document_id`
        FOREIGN KEY (`~db prefix~front_document_id`)
        REFERENCES `~db prefix~front_document` (`~db prefix~front_document_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~front_data_~db prefix~front_data_content_type_id`
        FOREIGN KEY (`~db prefix~front_data_content_type_id`)
        REFERENCES `~db prefix~front_data_content_type` (`~db prefix~front_data_content_type_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~front_data_~db prefix~front_controller_id`
        FOREIGN KEY (`~db prefix~front_controller_id`)
        REFERENCES `~db prefix~front_controller` (`~db prefix~front_controller_id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_navigation` (
    `~db prefix~front_navigation_id` CHAR(30) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `type` ENUM('list','tree') NOT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`~db prefix~front_navigation_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~session` (
    `~db prefix~session_id` CHAR(30) NOT NULL,
    `~db prefix~user_id` CHAR(30) NOT NULL,
    `user_agent` CHAR(32) NULL,
    `user_ip` CHAR(15) NULL,
    `is_ip_match` TINYINT(1) NOT NULL DEFAULT 0,
    `is_logged_in` TINYINT(1) NOT NULL DEFAULT 0,
    `life_span` INT UNSIGNED NULL,
    `timeout` INT UNSIGNED NULL,
    `creation_date` DATETIME NOT NULL,
    `last_impression_date` DATETIME NOT NULL,
    `valid_date` DATETIME NULL,
    PRIMARY KEY (`~db prefix~session_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~session_param` (
    `~db prefix~session_param_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `~db prefix~session_id` CHAR(30) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL,
    PRIMARY KEY (`~db prefix~session_param_id`),
    INDEX `fk_~db prefix~session_param_~db prefix~session_id_idx` (`~db prefix~session_id` ASC),
    CONSTRAINT `fk_~db prefix~session_param_~db prefix~session_id`
        FOREIGN KEY (`~db prefix~session_id`)
        REFERENCES `~db prefix~session` (`~db prefix~session_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~user` (
    `~db prefix~user_id` CHAR(30) NOT NULL,
    `status_id` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `first_name` VARCHAR(255) NULL,
    `last_name` VARCHAR(255) NULL,
    `patronymic_name` VARCHAR(255) NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone_code` VARCHAR(255) NULL,
    `phone` VARCHAR(255) NULL,
    `passwd` CHAR(32) NOT NULL,
    `reminder_key` CHAR(30) NULL,
    `reminder_time` DATETIME NULL,
    `creation_time` INT NULL,
    PRIMARY KEY (`~db prefix~user_id`),
    UNIQUE INDEX `~db prefix~user_email_unq` (`email` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~front_document_has_navigation` (
    `~db prefix~front_document_id` CHAR(30) NOT NULL,
    `~db prefix~front_navigation_id` CHAR(30) NOT NULL,
    PRIMARY KEY (`~db prefix~front_document_id`, `~db prefix~front_navigation_id`),
    INDEX `fk_~db prefix~front_document_has_navigation_~db prefix~front_navigation_id_idx` (`~db prefix~front_navigation_id` ASC),
    INDEX `fk_~db prefix~front_document_has_navigation_~db prefix~front_document_id_idx` (`~db prefix~front_document_id` ASC),
    CONSTRAINT `fk_~db prefix~front_document_has_navigation_~db prefix~front_document_id`
        FOREIGN KEY (`~db prefix~front_document_id`)
        REFERENCES `~db prefix~front_document` (`~db prefix~front_document_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~front_document_has_navigation_~db prefix~front_navigation_id`
        FOREIGN KEY (`~db prefix~front_navigation_id`)
        REFERENCES `~db prefix~front_navigation` (`~db prefix~front_navigation_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~back_user_has_section` (
    `~db prefix~back_user_id` CHAR(10) NOT NULL,
    `~db prefix~back_section_id` CHAR(10) NOT NULL,
    PRIMARY KEY (`~db prefix~back_user_id`, `~db prefix~back_section_id`),
    INDEX `fk_~db prefix~back_user_has_section_~db prefix~back_section_id_idx` (`~db prefix~back_section_id` ASC),
    INDEX `fk_~db prefix~back_user_has_section_~db prefix~back_user_id_idx` (`~db prefix~back_user_id` ASC),
    CONSTRAINT `fk_~db prefix~back_user_has_section_~db prefix~back_user_id`
        FOREIGN KEY (`~db prefix~back_user_id`)
        REFERENCES `~db prefix~back_user` (`~db prefix~back_user_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_~db prefix~back_user_has_section_~db prefix~back_section_id`
        FOREIGN KEY (`~db prefix~back_section_id`)
        REFERENCES `~db prefix~back_section` (`~db prefix~back_section_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `~db prefix~file_cache` (
    `~db prefix~file_cache_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `relative` VARCHAR(255) NOT NULL,
    `absolute` VARCHAR(255) NOT NULL,
    `creation_time` INT UNSIGNED NOT NULL,
    `size` MEDIUMINT UNSIGNED NOT NULL,
    `width` SMALLINT UNSIGNED NULL,
    `height` SMALLINT UNSIGNED NULL,
    `mime` VARCHAR(255) NULL,
    PRIMARY KEY (`~db prefix~file_cache_id`),
    UNIQUE INDEX `~db prefix~file_cache_relative_unq` (`relative` ASC),
    UNIQUE INDEX `~db prefix~file_cache_absolute_unq` (`absolute` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;
