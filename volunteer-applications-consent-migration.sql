-- Migrate the existing numeric consent flag to explicit yes/no values.
ALTER TABLE volunteer_applications
    MODIFY consent_given VARCHAR(3) NOT NULL;

UPDATE volunteer_applications
SET consent_given = CASE consent_given
    WHEN '1' THEN 'yes'
    WHEN '0' THEN 'no'
    ELSE consent_given
END;

ALTER TABLE volunteer_applications
    MODIFY consent_given ENUM('yes', 'no') NOT NULL;
