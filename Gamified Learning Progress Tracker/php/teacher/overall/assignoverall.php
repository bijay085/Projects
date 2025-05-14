<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Function to assign badges based on criteria
function assign_badges($conn, $userid, $attendance, $assignment, $performance, $result, $class_activity, $performance_rate, $behavior, $overall)
{
    $badges = [];

    // Assign badges based on criteria
    if ($attendance == 'full')
        $badges[] = 2;
    if ($assignment == 'submitted')
        $badges[] = 13;
    if ($performance == 'good')
        $badges[] = 1;
    if ($result == 'pass')
        $badges[] = 12;
    if ($class_activity >= 80)
        $badges[] = 6;
    if ($performance_rate >= 80)
        $badges[] = 7;
    if ($behavior >= 80)
        $badges[] = 8;
    if ($overall >= 80)
        $badges[] = 9;

    // Insert badges into tbl_user_badges if not already assigned
    foreach ($badges as $badgeid) {
        // Check if badge is already assigned
        $query = "SELECT 1 FROM tbl_user_badges WHERE userid = ? AND badgeid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userid, $badgeid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Badge not found, insert it
            $stmt = $conn->prepare("INSERT INTO tbl_user_badges (userid, badgeid) VALUES (?, ?)");
            $stmt->bind_param("ii", $userid, $badgeid);
            $stmt->execute();
            $stmt->close();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roleid = $_POST['roleid'];
    $attendance = $_POST['attendance'];
    $assignment = $_POST['assignment'];
    $performance = $_POST['performance'];
    $result = $_POST['result'];
    $class_activity = $_POST['class_activity'];
    $performance_rate = $_POST['performance_rate'];
    $behavior = $_POST['behavior'];
    $overall = $_POST['overall'];

    // Update or insert the overall record
    $query = "INSERT INTO tbl_overall (roleid, attendance, assignment, performance, result, class_activity, performance_rate, behavior, overall)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
              attendance = VALUES(attendance),
              assignment = VALUES(assignment),
              performance = VALUES(performance),
              result = VALUES(result),
              class_activity = VALUES(class_activity),
              performance_rate = VALUES(performance_rate),
              behavior = VALUES(behavior),
              overall = VALUES(overall)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssss", $roleid, $attendance, $assignment, $performance, $result, $class_activity, $performance_rate, $behavior, $overall);

    if ($stmt->execute()) {
        // Get the user id from tbl_users
        $query = "SELECT userid FROM tbl_users WHERE roleid = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $roleid);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if we got a result
        if ($result->num_rows > 0) {
            $user_row = $result->fetch_assoc();
            $userid = $user_row['userid'];

            // Assign badges
            assign_badges($conn, $userid, $attendance, $assignment, $performance, $result, $class_activity, $performance_rate, $behavior, $overall);
            echo "Overall performance and badges assigned successfully.";
            header('location: allstudents.php');
        } else {
            echo "No user found with the provided role ID.";
        }

        $stmt->close();
    } else {
        die("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
    }
} else {
    $roleid = $_GET['roleid'];

    // Fetch existing overall data if available
    $query = "SELECT * FROM tbl_overall WHERE roleid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $roleid);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize $row with default values if no record found
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $row = [
            'attendance' => 'none',
            'assignment' => 'not done',
            'performance' => 'bad',
            'result' => 'not attempted',
            'class_activity' => 0,
            'performance_rate' => 0,
            'behavior' => 0,
            'overall' => 0
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assign Overall Performance</title>
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
            max-width: 600px;
            margin: auto;
        }

        h1 {
            margin-top: 0;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input,
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            padding: 15px;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Assign Overall Performance</h1>
        <form method="POST" action="">
            <input type="hidden" name="roleid" value="<?php echo htmlspecialchars($roleid); ?>">

            <label for="attendance">Attendance:</label>
            <select id="attendance" name="attendance" required>
                <option value="full" <?php echo ($row['attendance'] == 'full') ? 'selected' : ''; ?>>Full</option>
                <option value="partial" <?php echo ($row['attendance'] == 'partial') ? 'selected' : ''; ?>>Partial
                </option>
                <option value="none" <?php echo ($row['attendance'] == 'none') ? 'selected' : ''; ?>>None</option>
            </select>

            <label for="assignment">Assignment:</label>
            <select id="assignment" name="assignment" required>
                <option value="submitted" <?php echo ($row['assignment'] == 'submitted') ? 'selected' : ''; ?>>Submitted
                </option>
                <option value="not submitted" <?php echo ($row['assignment'] == 'not submitted') ? 'selected' : ''; ?>>Not
                    Submitted</option>
            </select>

            <label for="performance">Performance:</label>
            <select id="performance" name="performance" required>
                <option value="good" <?php echo ($row['performance'] == 'good') ? 'selected' : ''; ?>>Good</option>
                <option value="average" <?php echo ($row['performance'] == 'average') ? 'selected' : ''; ?>>Average
                </option>
                <option value="bad" <?php echo ($row['performance'] == 'bad') ? 'selected' : ''; ?>>Bad</option>
            </select>

            <label for="result">Result:</label>
            <select id="result" name="result" required>
                <option value="pass" <?php echo ($row['result'] == 'pass') ? 'selected' : ''; ?>>Pass</option>
                <option value="fail" <?php echo ($row['result'] == 'fail') ? 'selected' : ''; ?>>Fail</option>
                <option value="not attempted" <?php echo ($row['result'] == 'not attempted') ? 'selected' : ''; ?>>Not
                    Attempted</option>
            </select>

            <label for="class_activity">Class Activity (%):</label>
            <input type="text" id="class_activity" name="class_activity"
                value="<?php echo htmlspecialchars($row['class_activity']); ?>" required pattern="\d+(\.\d{1,2})?"
                title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="performance_rate">Performance Rate (%):</label>
            <input type="text" id="performance_rate" name="performance_rate"
                value="<?php echo htmlspecialchars($row['performance_rate']); ?>" required pattern="\d+(\.\d{1,2})?"
                title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="behavior">Behavior (%):</label>
            <input type="text" id="behavior" name="behavior" value="<?php echo htmlspecialchars($row['behavior']); ?>"
                required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">

            <label for="overall">Overall (%):</label>
            <input type="text" id="overall" name="overall" value="<?php echo htmlspecialchars($row['overall']); ?>"
                readonly>

            <input type="submit" value="Assign Overall">
        </form>
    </div>
    <script>
        // Calculate overall percentage based on class activity, performance rate, and behavior
        document.querySelector('form').addEventListener('input', function () {
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
$conn->close();
?>