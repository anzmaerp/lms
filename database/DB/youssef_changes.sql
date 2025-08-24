alter table coupons
add column description text null; 

ALTER TABLE coupons DROP INDEX coupons_couponable_type_couponable_id_index;

ALTER TABLE coupons MODIFY couponable_id JSON NULL;

ALTER TABLE courses_bundles 
ADD created_by BIGINT UNSIGNED NULL;

ALTER TABLE courses_bundles 
ADD CONSTRAINT courses_bundles_created_by_fk FOREIGN KEY (created_by) REFERENCES users(id);