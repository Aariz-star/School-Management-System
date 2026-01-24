# Quick Reference Guide - CMS System

## What Each File Does

### Frontend Files
- **index.php** - Main page, all forms, navbar
- **script.js** - JavaScript to toggle between forms
- **styles.css** - All styling (black theme, responsive)

### Backend Files
- **student_register.php** - Handles student registration
- **teacher_assign.php** - Handles teacher assignments
- **attendance_record.php** - Handles attendance marking
- **grade_entry.php** - Handles grade entry
- **fee_management.php** - Handles fee processing

### Configuration
- **config.php** - Database connection

---

## Form Display - How It Works

### The JavaScript Magic (Only 5 lines matter)
```javascript
function showForm(formId) {
    document.querySelectorAll('.form-content').forEach(f => f.classList.remove('active'));
    document.getElementById(formId).classList.add('active');
}
```

### The CSS Magic
```css
.form-content { display: none; }
.form-content.active { display: block; }
```

### The HTML
```html
<button onclick="showForm('student')">Student Registration</button>
<form id="student" class="form-content active">...</form>
```

When you click button → JavaScript adds "active" to form → CSS shows it

---

## PHP Backend Pattern (Same for all forms)

```php
<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Get data from form
    $variable = $_POST['fieldname'];
    
    // 2. Validate data
    if (empty($variable)) {
        $_SESSION['error'] = "Error message";
        header("Location: index.php");
        exit;
    }
    
    // 3. Insert into database
    $stmt = $conn->prepare("INSERT INTO table_name VALUES (?, ?, ?)");
    $stmt->bind_param("type", $var1, $var2, $var3);
    
    // 4. Check if successful
    if ($stmt->execute()) {
        $_SESSION['success'] = "Success message!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    
    // 5. Redirect back
    header("Location: index.php");
    exit;
}
?>
```

---

## Adding a New Form - 3 Steps

### Step 1: Add HTML Form in index.php
```html
<form id="newform" class="form-content" action="newform.php" method="post">
    <h2>New Form Title</h2>
    <div class="form-grid">
        <input type="text" name="field1" required>
        <input type="text" name="field2" required>
    </div>
    <button class="submit-btn" type="submit">Submit</button>
</form>
```

### Step 2: Add Navigation Button
```html
<button class="nav-btn" onclick="showForm('newform')">New Form</button>
```

### Step 3: Create Backend File newform.php
(Copy the PHP pattern above and customize)

---

## Common Errors & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| Form not appearing | Wrong form ID | Check `id="..."` matches onclick |
| Data not saving | Missing database table | Create the table in MySQL |
| Blank page after submit | Missing `session_start()` | Add at top of PHP file |
| 404 error | Wrong action path | Check `action="filename.php"` |
| Notifications not showing | No session data | Check `header("Location: ...")` |

---

## Styling the Forms

All forms automatically get these styles from CSS:

```css
.form-content {
    background: dark gradient
    padding: 2.5rem
    border-radius: 12px
    border: cyan accent
    box-shadow: glowing effect
}

.form-content input {
    dark background
    cyan border on focus
    glass effect
}

.submit-btn {
    cyan gradient
    hover: glowing effect
    click: ripple effect
}
```

To customize: Edit the `.form-content`, `.form-grid`, `.submit-btn` classes in styles.css

---

## Testing Checklist

- [ ] Form appears when button clicked
- [ ] Form styling looks good
- [ ] Form data submits correctly
- [ ] Data appears in database
- [ ] Success message shows
- [ ] Success message auto-hides
- [ ] Empty form shows error
- [ ] Mobile view looks good

---

## Important Functions to Know

### JavaScript
```javascript
showForm(formId)  // Shows a form by ID, hides others
```

### PHP Functions Used
```php
$_POST[]          // Get form data
$_SESSION[]       // Store message for next page
session_start()   // Enable sessions
header()          // Redirect to another page
$conn->prepare()  // Prepare SQL statement
bind_param()      // Bind variables safely
execute()         // Run SQL query
```

### MySQL Table Basics
```sql
INSERT INTO table VALUES (...)    // Add data
SELECT * FROM table               // Get data
UPDATE table SET ...              // Change data
DELETE FROM table WHERE ...       // Remove data
```

---

## Step-by-Step: Submit a Form

1. User fills form fields
2. User clicks "Submit" button
3. Form's `action="file.php"` + `method="post"` triggers
4. PHP file receives data via `$_POST`
5. PHP validates the data
6. PHP inserts into database
7. PHP sets `$_SESSION['success']`
8. PHP redirects to index.php
9. index.php loads and displays notification
10. JavaScript hides notification after 5 seconds

---

## Files You Created This Session

✓ script.js - Form toggling
✓ teacher_assign.php - Teacher assignment backend
✓ attendance_record.php - Attendance backend
✓ grade_entry.php - Grade entry backend
✓ fee_management.php - Fee management backend
✓ BACKEND_GUIDE.md - Complete documentation
✓ SYSTEM_ARCHITECTURE.md - Architecture diagrams

---

## Common PHP Patterns

### Check if form submitted
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') { ... }
```

### Set success message
```php
$_SESSION['success'] = "Your message here!";
header("Location: index.php");
exit;
```

### Prepare & execute safely
```php
$stmt = $conn->prepare("INSERT INTO users VALUES (?, ?)");
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
```

### Loop through database results
```php
$res = $conn->query("SELECT * FROM users");
while($row = $res->fetch_assoc()) {
    echo "<option value='{$row['id']}'>{$row['name']}</option>";
}
```

---

## CSS Classes Reference

| Class | Use |
|-------|-----|
| `.form-content` | Form container |
| `.form-grid` | Input grid layout |
| `.form-content input` | Input field styling |
| `.submit-btn` | Submit button |
| `.nav-btn` | Navigation button |
| `.nav-btn.active` | Active nav button |
| `.notification` | Message container |
| `.notification-success` | Green notification |
| `.notification-error` | Red notification |

---

You're all set! Your CMS system is now fully functional with:
✓ 5 complete forms
✓ Beautiful black theme styling
✓ Responsive design
✓ Complete PHP backend
✓ Database integration
✓ Success/error notifications
✓ Form validation

Happy coding! 🚀
