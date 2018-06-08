DROP TABLE IF EXISTS `scheduler_log` ;
DROP TABLE IF EXISTS `scheduler_task` ;

-- -----------------------------------------------------
-- Table `scheduler_task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scheduler_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dislpay_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `schedule` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `status_id` int(11) unsigned NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `last_run` timestamp NULL DEFAULT NULL,
  `next_run` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `scheduler_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `scheduler_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scheduler_task_id` int(11) unsigned NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  `output` text COLLATE utf8_unicode_ci NOT NULL,
  `error` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_scheduler_log_scheduler_task_id` (`scheduler_task_id`),
  CONSTRAINT `fk_scheduler_log_scheduler_task_id`
    FOREIGN KEY (`scheduler_task_id`)
    REFERENCES `scheduler_task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
