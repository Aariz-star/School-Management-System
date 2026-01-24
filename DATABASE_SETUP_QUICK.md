# Quick Database Setup for Teacher Management Module

## Execute These Commands in Your MySQL Database

### Copy and paste this entire block into your MySQL client (phpMyAdmin, MySQL Workbench, or command line):

```sql
-- Step 1: Add columns to teachers table
ALTER TABLE teachers ADD COLUMN (
    father_name VARCHAR(100) DEFAULT '',
    salary DECIMAL(10, 2) DEFAULT 0,
    phone VARCHAR(20) DEFAULT '',
    email VARCHAR(100) DEFAULT '',
    remaining_payment DECIMAL(10, 2) DEFAULT 0
);

-- Step 2: Create teacher_subjects junction table
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    UNIQUE KEY unique_teacher_subject (teacher_id, subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);

-- Step 3: Verify the changes
DESCRIBE teachers;
DESCRIBE teacher_subjects;
```

## Expected Output

After running the commands above, you should see:

### teachers table structure:
```
Field               Type            Null    Key     Default
id                  int             NO      PRI     NULL
name                varchar(100)    YES             NULL
father_name         varchar(100)    YES             NULL        [NEW]
salary              decimal(10,2)   YES             0           [NEW]
phone               varchar(20)     YES             NULL        [NEW]
email               varchar(100)    YES             NULL        [NEW]
remaining_payment   decimal(10,2)   YES             0           [NEW]
```

### teacher_subjects table structure:
```
Field               Type    Null    Key     Default
id                  int     NO      PRI     NULL
teacher_id          int     NO      MUL     NULL
subject_id          int     NO      MUL     NULL
```

## Troubleshooting

### If you get an error "Unknown column"
- The column might already exist (it's fine to proceed)
- Run: `ALTER TABLE teachers MODIFY COLUMN salary DECIMAL(10,2);` to fix

### If you get an error about foreign keys
- Make sure `subjects` and `teachers` tables exist
- Check: `SHOW TABLES;` to list all tables

### If teacher_subjects table won't create
- Drop it first: `DROP TABLE IF EXISTS teacher_subjects;`
- Then run the CREATE TABLE command again

## After Setup

You're ready to:
1. Navigate to "Add Teacher" form in the CMS
2. Add teachers with multiple subjects
3. View all teachers in the Teachers Directory
4. Add new classes/grades

That's it! The module will handle all the data insertion and retrieval automatically.
