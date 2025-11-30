ALTER TABLE candidates 
ADD COLUMN immediate_joiner TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = Yes, 0 = No' AFTER notice_period,
ADD COLUMN open_to_opportunities TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Yes (open to jobs), 0 = No' AFTER immediate_joiner;