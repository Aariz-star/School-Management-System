<?php
session_start();
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $name = trim($_POST['name']);
    $father_name = trim($_POST['father_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $salary = floatval($_POST['salary']);
    $remaining = floatval($_POST['remaining_payment']);
    $subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];

    // Update Teacher Info
    $stmt = $conn->prepare("UPDATE teachers SET name=?, father_name=?, phone=?, email=?, salary=?, remaining_payment=? WHERE id=?");
    $stmt->bind_param("ssssddi", $name, $father_name, $phone, $email, $salary, $remaining, $teacher_id);
    
    if ($stmt->execute()) {
        // Update Subjects (Delete all old, insert new)
        $conn->query("DELETE FROM teacher_subjects WHERE teacher_id = $teacher_id");
        
        if (!empty($subjects)) {
            $stmt_sub = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
            foreach ($subjects as $sub_id) {
                $stmt_sub->bind_param("ii", $teacher_id, $sub_id);
                $stmt_sub->execute();
            }
            $stmt_sub->close();
        }
        $_SESSION['success'] = "Teacher updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating teacher: " . $conn->error;
    }
    $stmt->close();
    header("Location: index.php");
    exit;
}

// Fetch Teacher Data
$stmt = $conn->prepare("SELECT * FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if (!$teacher) die("Teacher not found.");

// Fetch Assigned Subjects
$assigned_subjects = [];
$res = $conn->query("SELECT subject_id FROM teacher_subjects WHERE teacher_id = $id");
while($row = $res->fetch_assoc()) {
    $assigned_subjects[] = $row['subject_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Teacher</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <form class="form-content active" method="post">
            <h2>Edit Teacher</h2>
            <input type="hidden" name="teacher_id" value="<?= $teacher['id'] ?>">

            <div class="form-grid">
                <label>Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" required>
                
                <label>Father's Name</label>
                <input type="text" name="father_name" value="<?= htmlspecialchars($teacher['father_name']) ?>" required>
                
                <label>Phone</label>
                <input type="tel" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" required>
                
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" required>
                
                <label>Salary</label>
                <input type="number" name="salary" value="<?= $teacher['salary'] ?>" step="0.01" required>
                
                <label>Remaining Payment</label>
                <input type="number" name="remaining_payment" value="<?= $teacher['remaining_payment'] ?>" step="0.01" required>

                <div class="form-full-width">
                    <label class="subjects-label">Assigned Subjects:</label>
                    <div class="subjects-grid">
                        <?php
                        $res = $conn->query("SELECT id, name FROM subjects ORDER BY name");
                        if ($res && $res->num_rows > 0) {
                            while($row = $res->fetch_assoc()) {
                                $checked = in_array($row['id'], $assigned_subjects) ? 'checked' : '';
                                echo "<label class='subject-checkbox-label'>";
                                echo "<input type='checkbox' name='subjects[]' value='{$row['id']}' $checked>";
                                echo htmlspecialchars($row['name']);
                                echo "</label>";
                            }
                        } else {
                            echo "<p class='no-subjects-msg'>No subjects found.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-buttons">
                <button class="submit-btn" type="submit">Update Teacher</button>
                <a href="index.php" class="btn btn-reset" style="text-align:center; text-decoration:none; display:inline-block;">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>