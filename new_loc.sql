ALTER TABLE job_postings 
ADD COLUMN interview_pref_loca VARCHAR(255) NULL AFTER interview_location,
ADD INDEX idx_interview_pref_loca (interview_pref_loca);