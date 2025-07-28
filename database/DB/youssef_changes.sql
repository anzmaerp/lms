alter table orders 
add column  fawaterk_status longtext DEFAULT null;


ALTER TABLE coupons 
ADD COLUMN instructor_id BIGINT(20) UNSIGNED NOT NULL,
ADD CONSTRAINT coupons_instructor_id_fk FOREIGN KEY (instructor_id) REFERENCES users(id);
