ALTER TABLE candidates
ADD preferred_locations VARCHAR(255) DEFAULT NULL,
ADD preferred_languages VARCHAR(255) DEFAULT NULL;

ALTER TABLE candidates
ADD profile_pic VARCHAR(255) DEFAULT NULL;