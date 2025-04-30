--------
-- MySQL
CREATE DATABASE softplan_task_api;

use softplan_task_api;

CREATE TABLE `project` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `task` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` VARCHAR(256) COLLATE utf8mb4_unicode_ci NULL,
  `project_id` INT UNSIGNED NULL,
  `started` TIMESTAMP NULL,
  `finished` TIMESTAMP NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `task`
  ADD CONSTRAINT `fk_task_project` FOREIGN KEY (`project_id`)
  REFERENCES `project` (`id`);

commit;
