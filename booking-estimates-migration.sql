ALTER TABLE booking_requests
    ADD COLUMN IF NOT EXISTS estimate_minimum BIGINT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS estimate_maximum BIGINT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS estimate_currency CHAR(3) NULL,
    ADD COLUMN IF NOT EXISTS estimate_scope VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS estimate_quantity INT UNSIGNED NULL;

CREATE TABLE IF NOT EXISTS declined_booking_estimates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    company VARCHAR(180) NULL,
    service VARCHAR(120) NOT NULL,
    project_scope VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    estimate_minimum BIGINT UNSIGNED NOT NULL,
    estimate_maximum BIGINT UNSIGNED NOT NULL,
    currency CHAR(3) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    follow_up_consent TINYINT(1) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_declined_booking_estimates_created_at (created_at),
    INDEX idx_declined_booking_estimates_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE declined_booking_estimates
    ADD COLUMN IF NOT EXISTS quantity INT UNSIGNED NOT NULL DEFAULT 1;
