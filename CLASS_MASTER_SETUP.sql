-- Add Class Master column to classes table
ALTER TABLE classes ADD COLUMN class_master_id INT NULL;

-- Link it to teachers table
ALTER TABLE classes ADD CONSTRAINT fk_class_master FOREIGN KEY (class_master_id) REFERENCES teachers(id) ON DELETE SET NULL;