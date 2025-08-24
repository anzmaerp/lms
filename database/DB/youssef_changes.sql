alter table coupons
add column description text null; 

ALTER TABLE coupons DROP INDEX coupons_couponable_type_couponable_id_index;

ALTER TABLE coupons MODIFY couponable_id JSON NULL;

ALTER TABLE courses_bundles 
ADD created_by BIGINT UNSIGNED NULL;

ALTER TABLE courses_bundles 
ADD CONSTRAINT courses_bundles_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id);

ALTER TABLE `uc__templates`
MODIFY `user_id` JSON NULL;

ALTER TABLE `uc__templates`
ADD COLUMN `created_by` BIGINT(20) UNSIGNED NULL ,
ADD CONSTRAINT `uc_templates_created_by_fk`
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;