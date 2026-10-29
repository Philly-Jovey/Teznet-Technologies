CREATE TABLE booking_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    email VARCHAR(254) NOT NULL,
    company VARCHAR(180) NULL,
    service VARCHAR(120) NOT NULL,
    description TEXT NULL,
    request_date DATE NOT NULL,
    request_time TIME NOT NULL,
    request_timezone VARCHAR(64) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_booking_requests_created_at (created_at),
    INDEX idx_booking_requests_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
