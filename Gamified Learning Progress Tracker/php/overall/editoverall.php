<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Check if oid is provided
if (isset($_GET['oid'])) {
    $oid = $_GET['oid'];

    // Fetch the existing data for the given oid
    $query = "SELECT * FROM tbl_overall WHERE oid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $oid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo "No record found with oid: " . $oid;
        exit;
    }
} else {
    echo "No oid provided!";
    exit;
}

// Handle form submission to update the data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleid = $_POST['roleid'];
    $fullname = $_POST['fullname'];
    $attendance = $_POST['attendance'];
    $assignment = $_POST['assignment'];
    $performance = $_POST['performance'];
    $result = $_POST['result'];
    $description = $_POST['description'];

    // Update the record in the database
    $updateQuery = "UPDATE tbl_overall SET roleid = ?, fullname = ?, attendance = ?, assignment = ?, performance = ?, result = ?, description = ? WHERE oid = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('sssssssi', $roleid, $fullname, $attendance, $assignment, $performance, $result, $description, $oid);

    if ($updateStmt->execute()) {
        echo "Record updated successfully!";
        header("location: overall_index.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Overall</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ccc; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Overall Record</h2>
    <form method="post">
        <div class="form-group">
            <label for="roleid">Role ID</label>
            <input type="text" id="roleid" name="roleid" value="<?php echo htmlspecialchars($row['roleid']); ?>" required>
        </div>
        <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="attendance">Attendance</label>
            <select id="attendance" name="attendance" required>
                <option value="full" <?php echo ($row['attendance'] == 'full') ? 'selected' : ''; ?>>Full</option>
                <option value="half" <?php echo ($row['attendance'] == 'half') ? 'selected' : ''; ?>>Half</option>
                <option value="none" <?php echo ($row['attendance'] == 'none') ? 'selected' : ''; ?>>None</option>
            </select>
        </div>
        <div class="form-group">
            <label for="assignment">Assignment</label>
            <select id="assignment" name="assignment" required>
                <option value="submitted" <?php echo ($row['assignment'] == 'submitted') ? 'selected' : ''; ?>>Submitted</option>
                <option value="incomplete" <?php echo ($row['assignment'] == 'incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                <option value="not done" <?php echo ($row['assignment'] == 'not done') ? 'selected' : ''; ?>>Not Done</option>
            </select>
        </div>
        <div class="form-group">
            <label for="performance">Performance</label>
            <select id="performance" name="performance" required>
                <option value="good" <?php echo ($row['performance'] == 'good') ? 'selected' : ''; ?>>Good</option>
                <option value="average" <?php echo ($row['performance'] == 'average') ? 'selected' : ''; ?>>Average</option>
                <option value="bad" <?php echo ($row['performance'] == 'bad') ? 'selected' : ''; ?>>Bad</option>
            </select>
        </div>
        <div class="form-group">
            <label for="result">Result</label>
            <select id="result" name="result" required>
                <option value="pass" <?php echo ($row['result'] == 'pass') ? 'selected' : ''; ?>>Pass</option>
                <option value="fail" <?php echo ($row['result'] == 'fail') ? 'selected' : ''; ?>>Fail</option>
                <option value="not attempted" <?php echo ($row['result'] == 'not attempted') ? 'selected' : ''; ?>>Not Attempted</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
        </div>
        <button type="submit">Update</button>
    </form>
</div>
</body>
</html>
