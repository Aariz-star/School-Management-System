-- Add book_name column to class_subjects table
ALTER TABLE class_subjects ADD COLUMN book_name VARCHAR(255) NULL DEFAULT NULL;