# Backend & PHP Logic Guide - CMS System

## Overview
Your CMS system now has a complete frontend-backend structure that works together seamlessly.

---

## How It Works (Simple Explanation)

### Frontend (HTML + JavaScript + CSS)
1. **User clicks a navigation button** → JavaScript shows the corresponding form
2. **User fills the form and clicks submit** → Form data is sent to a PHP file
3. **PHP file processes the data** → Saves to database & sends response back

### Backend (PHP)
1. **Receives form data** from the frontend
2. **Validates the data** (checks if all required fields are filled, etc.)
3. **Inserts/Updates data** in the database
4. **Redirects back** to index.php with success/error message

---

## Architecture Breakdown

### 1. **Frontend - index.php**
- Contains all 5 forms (Student, Teacher, Attendance, Grade, Fee)
- JavaScript function `showForm()` toggles between forms
- Each form has `action="filename.php"` that points to its backend handler
- Displays success/error messages from sessions

### 2. **JavaScript - script.js**
```javascript
function showForm(formId) {
    // Hide all forms, show only the selected one
    // Add/remove active class to highlight the clicked button
}
```

This is the **ONLY** JavaScript you need for form switching!

### 3. **Backend Files - Each form has its own PHP file**

#### **student_register.php**
- Receives: full_name, admission_date, guardian_name, contact_number, email, class_id
- Validates: All fields required, email format valid, no duplicate emails
- Inserts: INTO students table
- Returns: Success message

#### **teacher_assign.php**
- Receives: teacher_id, subject_id, class_id, academic_year
- Validates: All fields selected
- Inserts: INTO teacher_assignments table
- Returns: Success message

#### **attendance_record.php**
- Receives: class_id, attendance_date, student_id, status
- Validates: All fields required
- Inserts: INTO attendance table
- Returns: Success message

#### **grade_entry.php**
- Receives: student_id, subject_id, marks_obtained, total_marks, semester, academic_year
- Validates: Marks within range, marks_obtained ≤ total_marks
- Inserts: INTO grades table (calculates percentage automatically)
- Returns: Success message

#### **fee_management.php**
- Receives: student_id, fee_amount, fee_date, payment_status, payment_method, remarks
- Validates: All required fields filled
- Inserts: INTO fee_management table
- Returns: Success message

---

## How To Display Forms and Handle Styling

### Problem You Had:
Forms weren't displaying because:
1. All forms had `display: none` initially
2. Only the "active" form had `display: block`
3. JavaScript wasn't being called properly

### Solution Implemented:
1. **CSS handles visibility** - `.form-content.active { display: block; }`
2. **JavaScript toggles the "active" class** - When button clicked, active form gets hidden, new form gets active class
3. **Styling is automatic** - All active forms are styled the same way with the black theme

### Form Display Flow:
```
User clicks "Student Registration" button
    ↓
JavaScript showForm('student') runs
    ↓
All .form-content elements get active class removed
    ↓
#student form gets active class added
    ↓
CSS shows #student form (display: block)
    ↓
User sees styled form
```

---

## Step-by-Step: How to Add a New Form

Let's say you want to add a "Hostel Management" form:

### Step 1: Add HTML Form in index.php
```html
<form id="hostel" class="form-content" action="hostel_management.php" method="post">
    <h2>Hostel Management</h2>
    <div class="form-grid">
        <select name="student_id" required>
            <option value="">Select Student</option>
            <?php
            $res = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
            while($row = $res->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
            }
            ?>
        </select>
        <input type="text" name="room_number" placeholder="Room Number" required>
        <input type="text" name="block" placeholder="Block (A, B, C, etc.)" required>
        <input type="date" name="check_in_date" required>
    </div>
    <button class="submit-btn" type="submit">Register Hostel</button>
</form>
```

### Step 2: Add Button in Navigation
```html
<button class="nav-btn" onclick="showForm('hostel')">Hostel Management</button>
```

### Step 3: Create Backend File - hostel_management.php
```php
<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $room_number = trim($_POST['room_number'] ?? '');
    $block = trim($_POST['block'] ?? '');
    $check_in_date = $_POST['check_in_date'] ?? '';
    
    $errors = [];
    
    if ($student_id <= 0)       $errors[] = "Please select a student.";
    if (empty($room_number))    $errors[] = "Room number is required.";
    if (empty($block))          $errors[] = "Block is required.";
    if (empty($check_in_date))  $errors[] = "Check-in date is required.";
    
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO hostel_management
            (student_id, room_number, block, check_in_date)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $student_id, $room_number, $block, $check_in_date);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✓ Hostel registered successfully!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "✗ Database error: " . $conn->error;
            header("Location: index.php");
            exit;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "✗ " . implode("\n✗ ", $errors);
        header("Location: index.php");
        exit;
    }
}
?>
```

That's it! The new form will automatically get all the styling from the CSS class `.form-content`

---

## Common Questions

### Q: How do I make the form NOT visible initially?
A: Change `class="form-content active"` to `class="form-content"` (remove "active")

### Q: How do I add validation (like email format)?
A: Add this in the PHP backend file:
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format!";
    header("Location: index.php");
    exit;
}
```

### Q: How do I customize the form styling?
A: Edit these CSS classes:
- `.form-content` - The form container
- `.form-grid` - The input grid layout
- `.form-content input` - Input field styling
- `.submit-btn` - Submit button styling

### Q: Can I make a form submit without page reload (AJAX)?
A: Yes, but that's more advanced. For now, page reload is fine since you get clear success/error messages.

### Q: What if the form doesn't show after clicking button?
A: Check:
1. Is the form ID correct? (`id="student"`)
2. Is the onclick function correct? (`onclick="showForm('student'"`)
3. Are you calling the right form ID in JavaScript?
4. Is script.js loaded? (Check `<script src="script.js"></script>` at bottom of index.php)

---

## Files Created/Modified

**Created:**
- `script.js` - JavaScript for form toggling
- `teacher_assign.php` - Teacher assignment backend
- `attendance_record.php` - Attendance backend
- `grade_entry.php` - Grade entry backend
- `fee_management.php` - Fee management backend

**Modified:**
- `index.php` - Added session handling and notification display + completed all 5 forms
- `student_register.php` - Updated to use sessions
- `styles.css` - Added notification styles

---

## Testing Your Forms

1. Refresh your browser
2. Click a navigation button (e.g., "Student Registration")
3. The form should appear with styling
4. Fill in the form and click submit
5. You should see a green success message at top right
6. Data is saved in the database

---

## Key Takeaways

✓ **JavaScript only handles showing/hiding forms** - Very simple!
✓ **PHP handles all the heavy lifting** - Validation, database, security
✓ **CSS handles all the styling** - Forms automatically styled via `.form-content` class
✓ **Sessions pass messages** - Success/error messages are stored in sessions
✓ **All forms follow same pattern** - Easy to add new forms

You now have a complete, professional CMS system! 🎉
