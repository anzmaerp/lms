ALTER TABLE COURSES_CURRICULUMS 
MODIFY COLUMN  type enum('video','yt_link','vm_link','article','pdf','url') NOT NULL;

CREATE TABLE IF NOT EXISTS `bundle_instructor` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `bundle_id` BIGINT(20) UNSIGNED NOT NULL,
    `instructor_id` BIGINT(20) UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `bundle_instructor_bundle_id_index` (`bundle_id`),
    KEY `bundle_instructor_instructor_id_index` (`instructor_id`),
    CONSTRAINT `bundle_instructor_bundle_id_fk` FOREIGN KEY (`bundle_id`)
        REFERENCES `courses_bundles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `bundle_instructor_instructor_id_fk` FOREIGN KEY (`instructor_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `courses_bundles`
MODIFY `instructor_id` bigint(20) unsigned NULL;

