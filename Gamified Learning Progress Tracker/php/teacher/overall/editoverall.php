<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Function to assign badges based on criteria
function assign_badges($conn, $roleid, $attendance, $assignment, $performance, $result, $class_activity, $performance_rate, $behavior, $overall) {
    $badges = [];

    // Assign Full Attendance Badge
    if ($attendance == 'full') {
        $badges[] = 2;
    }

    // Assign Submitted Assignment Badge
    if ($assignment == 'submitted') {
        $badges[] = 13;
    }

    // Assign Good Performance Badge
    if ($performance == 'good') {
        $badges[] = 1;
    }

    // Assign Pass Result Badge
    if ($result == 'pass') {
        $badges[] = 12;
    }

    // Assign badges based on percentage criteria
    if ($class_activity >= 80) {
        $badges[] = 6; // Assuming 6 is the related badge ID
    }

    if ($performance_rate >= 80) {
        $badges[] = 7; // Assuming 7 is the related badge ID
    }

    if ($behavior >= 80) {
        $badges[] = 8; // Assuming 8 is the related badge ID
    }

    if ($overall >= 80) {
        $badges[] = 9; // Assuming 9 is the related badge ID
    }

    // Insert badges into tbl_user_badges
    foreach ($badges as $badgeid) {
        $stmt = $conn->prepare("INSERT INTO tbl_user_badges (roleid, badgeid) VALUES (?, ?) ON DUPLICATE KEY UPDATE badgeid = VALUES(badgeid)");
        $stmt->bind_param("si", $roleid, $badgeid);
        $stmt->execute();
        $stmt->close();
    }
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $oid = $_POST['oid'];
    $roleid = $_POST['roleid'];
    $fullname = $_POST['fullname'];
    $attendance = $_POST['attendance'];
    $assignment = $_POST['assignment'];
    $performance = $_POST['performance'];
    $result = $_POST['result'];
    $description = $_POST['description'];
    $programming_language = $_POST['programming_language'];
    $class_activity = $_POST['class_activity'];
    $performance_rate = $_POST['performance_rate'];
    $behavior = $_POST['behavior'];
    $overall = $_POST['overall'];

    // Update the record
    $query = "UPDATE tbl_overall SET roleid = ?, fullname = ?, attendance = ?, assignment = ?, performance = ?, result = ?, description = ?, programming_language = ?, class_activity = ?, performance_rate = ?, behavior = ?, overall = ? WHERE oid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssssdddi", $roleid, $fullname, $attendance, $assignment, $performance, $result, $description, $programming_language, $class_activity, $performance_rate, $behavior, $overall, $oid);

    if ($stmt->execute()) {
        // Assign badges
        assign_badges($conn, $roleid, $attendance, $assignment, $performance, $result, $class_activity, $performance_rate, $behavior, $overall);
        header("Location: overallindex.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $stmt->close();
} else {
    // Fetch the record to be edited
    $oid = $_GET['oid'];
    $query = "SELECT * FROM tbl_overall WHERE oid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $oid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Overall Performance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 50%;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        input[type="text"], textarea, select {
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Overall Performance</h2>
        <form action="editoverall.php" method="POST">
            <input type="hidden" name="oid" value="<?php echo htmlspecialchars($row['oid']); ?>">

            <label for="roleid">Role ID:</label>
            <input type="text" id="roleid" name="roleid" value="<?php echo htmlspecialchars($row['roleid']); ?>" required>

            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>" required>

            <label for="attendance">Attendance:</label>
            <select id="attendance" name="attendance" required>
                <option value="full" <?php echo ($row['attendance'] == 'full') ? 'selected' : ''; ?>>Full</option>
                <option value="half" <?php echo ($row['attendance'] == 'half') ? 'selected' : ''; ?>>Half</option>
                <option value="none" <?php echo ($row['attendance'] == 'none') ? 'selected' : ''; ?>>None</option>
            </select>

            <label for="assignment">Assignment:</label>
            <select id="assignment" name="assignment" required>
                <option value="submitted" <?php echo ($row['assignment'] == 'submitted') ? 'selected' : ''; ?>>Submitted</option>
                <option value="incomplete" <?php echo ($row['assignment'] == 'incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                <option value="not done" <?php echo ($row['assignment'] == 'not done') ? 'selected' : ''; ?>>Not Done</option>
            </select>

            <label for="performance">Performance:</label>
            <select id="performance" name="performance" required>
                <option value="good" <?php echo ($row['performance'] == 'good') ? 'selected' : ''; ?>>Good</option>
                <option value="average" <?php echo ($row['performance'] == 'average') ? 'selected' : ''; ?>>Average</option>
                <option value="bad" <?php echo ($row['performance'] == 'bad') ? 'selected' : ''; ?>>Bad</option>
            </select>

            <label for="result">Result:</label>
            <select id="result" name="result" required>
                <option value="pass" <?php echo ($row['result'] == 'pass') ? 'selected' : ''; ?>>Pass</option>
                <option value="fail" <?php echo ($row['result'] == 'fail') ? 'selected' : ''; ?>>Fail</option>
                <option value="not attempted" <?php echo ($row['result'] == 'not attempted') ? 'selected' : ''; ?>>Not Attempted</option>
            </select>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($row['description']); ?></textarea>

            <label for="programming_language">Programming Language:</label>
            <input type="text" id="programming_language" name="programming_language" value="<?php echo htmlspecialchars($row['programming_language']); ?>" required>

            <label for="class_activity">Class Activity (%):</label>
            <input type="text" id="class_activity" name="class_activity" value="<?php echo htmlspecialchars($row['class_activity']); ?>" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="performance_rate">Performance Rate (%):</label>
            <input type="text" id="performance_rate" name="performance_rate" value="<?php echo htmlspecialchars($row['performance_rate']); ?>" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="behavior">Behavior (%):</label>
            <input type="text" id="behavior" name="behavior" value="<?php echo htmlspecialchars($row['behavior']); ?>" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="overall">Overall (%):</label>
            <input type="text" id="overall" name="overall" value="<?php echo htmlspecialchars($row['overall']); ?>" readonly>

            <input type="submit" value="Update Overall">
        </form>
    </div>
    <script>
        // Calculate overall percentage based on class activity, performance rate, and behavior
        document.querySelector('form').addEventListener('input', function() {
            var classActivity = parseFloat(document.getElementById('class_activity').value) || 0;
            var performanceRate = parseFloat(document.getElementById('performance_rate').value) || 0;
            var behavior = parseFloat(document.getElementById('behavior').value) || 0;

            var overall = (classActivity + performanceRate + behavior) / 3;
            document.getElementById('overall').value = overall.toFixed(2);
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
