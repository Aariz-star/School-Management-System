<?php
session_start();
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $guardian_id = intval($_POST['guardian_id']);
    
    // Student Data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact_number']);
    $class_id = intval($_POST['class_id']);
    
    // Guardian Data
    $g_name = trim($_POST['guardian_name']);
    $g_contact = trim($_POST['guardian_contact']);
    $g_email = trim($_POST['guardian_email']);
    $g_relation = trim($_POST['relationship']);
    $g_address = trim($_POST['guardian_address']);

    // Update Guardian
    $stmt_g = $conn->prepare("UPDATE guardians SET guardian_name=?, contact_number=?, email=?, address=?, relationship_to_student=? WHERE id=?");
    $stmt_g->bind_param("sssssi", $g_name, $g_contact, $g_email, $g_address, $g_relation, $guardian_id);
    $stmt_g->execute();
    $stmt_g->close();

    // Update Student
    $stmt_s = $conn->prepare("UPDATE students SET full_name=?, email=?, contact_number=?, class_id=? WHERE id=?");
    $stmt_s->bind_param("sssii", $full_name, $email, $contact, $class_id, $student_id);
    
    if ($stmt_s->execute()) {
        $_SESSION['success'] = "Student details updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating student: " . $conn->error;
    }
    $stmt_s->close();
    
    header("Location: index.php");
    exit;
}

// Fetch Existing Data
$sql = "SELECT s.*, g.guardian_name, g.contact_number as g_contact, g.email as g_email, g.address as g_address, g.relationship_to_student 
        FROM students s 
        LEFT JOIN guardians g ON s.guardian_id = g.id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <form class="form-content active" method="post">
            <h2>Edit Student Details</h2>
            <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
            <input type="hidden" name="guardian_id" value="<?= $student['guardian_id'] ?>">

            <div class="form-grid">
                <!-- Student Info -->
                <div class="form-full-width"><h3 style="color:#00d4ff; border-bottom:1px solid #333; padding-bottom:5px;">Student Information</h3></div>
                
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                
                <label>Student Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>">
                
                <label>Student Contact</label>
                <input type="tel" name="contact_number" value="<?= htmlspecialchars($student['contact_number']) ?>">
                
                <label>Class</label>
                <select name="class_id" required>
                    <?php
                    $res = $conn->query("SELECT id, name FROM classes ORDER BY name");
                    while($row = $res->fetch_assoc()) {
                        $selected = ($row['id'] == $student['class_id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                    }
                    ?>
                </select>

                <!-- Guardian Info -->
                <div class="form-full-width"><h3 style="color:#00d4ff; border-bottom:1px solid #333; padding-bottom:5px; margin-top:20px;">Guardian Information</h3></div>

                <label>Guardian Name</label>
                <input type="text" name="guardian_name" value="<?= htmlspecialchars($student['guardian_name']) ?>" required>
                
                <label>Guardian Contact</label>
                <input type="tel" name="guardian_contact" value="<?= htmlspecialchars($student['g_contact']) ?>" required>
                
                <label>Guardian Email</label>
                <input type="email" name="guardian_email" value="<?= htmlspecialchars($student['g_email']) ?>">
                
                <label>Relationship</label>
                <input type="text" name="relationship" value="<?= htmlspecialchars($student['relationship_to_student']) ?>" required>
                
                <label>Address</label>
                <textarea name="guardian_address" rows="2"><?= htmlspecialchars($student['g_address']) ?></textarea>
            </div>

            <div class="form-buttons">
                <button class="submit-btn" type="submit">Update Details</button>
                <a href="index.php" class="btn btn-reset" style="text-align:center; text-decoration:none; display:inline-block;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>