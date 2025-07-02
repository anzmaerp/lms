ALTER TABLE orders ADD (
	payment_file_path varchar(255) NULL,
	payment_acceptnce enum('Y','N','P') default 'Y' NOT NULL,
	accept_by bigint UNSIGNED NULL,
	CONSTRAINT orders_accept_by_fk FOREIGN KEY (accept_by) REFERENCES users(id)
);

ALTER TABLE courses_enrollments ADD (
	is_paid enum('Y','N','P') DEFAULT 'Y' NOT NULL
);

ALTER TABLE orders ADD (
	payment_type enum('online','offline') DEFAULT 'online' NOT NULL
);

ALTER TABLE courses_enrollments ADD (
	order_id bigint UNSIGNED NULL
);

ALTER TABLE orders ADD (
	hesabe_status LONGTEXT 
);

ALTER TABLE `orders` CHANGE `payment_type` `payment_type_m` ENUM('online','offline') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'online';