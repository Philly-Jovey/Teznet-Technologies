-- Application metadata only. Uploaded documents belong in a private directory
-- outside the web document root configured with TEZNET_APPLICATION_UPLOAD_DIR.
CREATE TABLE volunteer_applications (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    opportunity_type ENUM('internship', 'volunteering') NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    education_level VARCHAR(40) NOT NULL,
    institution VARCHAR(180) NULL,
    department VARCHAR(80) NOT NULL,
    preferred_duration VARCHAR(40) NULL,
    availability VARCHAR(30) NULL,
    motivation TEXT NOT NULL,
    documents_json JSON NOT NULL,
    consent_given TINYINT(1) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_volunteer_applications_created_at (created_at),
    INDEX idx_volunteer_applications_email (email),
    INDEX idx_volunteer_applications_opportunity (opportunity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
