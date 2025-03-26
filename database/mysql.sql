--------
-- MySQL
CREATE DATABASE softplan_task_api;

use softplan_task_api;

CREATE TABLE `project` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `task` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tags` varchar(256) COLLATE utf8mb4_unicode_ci NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

commit;
