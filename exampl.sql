ALTER TABLE candidates
ADD preferred_locations VARCHAR(255) DEFAULT NULL,
ADD preferred_languages VARCHAR(255) DEFAULT NULL;

ALTER TABLE candidates
ADD profile_pic VARCHAR(255) DEFAULT NULL;

ALTER TABLE employers
ADD COLUMN job_post_credits INT NOT NULL DEFAULT 0,
ADD COLUMN database_credits INT NOT NULL DEFAULT 0,
DROP COLUMN credits;

ALTER TABLE credit_transactions
ADD COLUMN credit_type ENUM('job_post', 'database') NOT NULL DEFAULT 'job_post';

ALTER TABLE employers
ADD COLUMN last_credit_reset DATE NULL;
