-- Create table to link Classes and Subjects
CREATE TABLE IF NOT EXISTS class_subjects (
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    PRIMARY KEY (class_id, subject_id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Note: This ensures that if you delete a class, the subject links are also deleted automatically.