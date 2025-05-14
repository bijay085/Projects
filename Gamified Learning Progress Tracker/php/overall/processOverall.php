<?php
// Include your database connection file
include 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary

// Check if form is submitted and all required fields are present
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['roleid'], $_POST['attendance'], $_POST['assignment'], $_POST['performance'], $_POST['result'], $_POST['description'], $_POST['programming_language'], $_POST['class_activity'], $_POST['performance_rate'], $_POST['behavior'], $_POST['overall'])) {
    
    // Sanitize and validate inputs
    $roleid = $_POST['roleid'];
    $attendance = $_POST['attendance'];
    $assignment = $_POST['assignment'];
    $performance = $_POST['performance'];
    $result = $_POST['result'];
    $description = $_POST['description'];
    $programming_language = $_POST['programming_language'];
    $class_activity = floatval($_POST['class_activity']);
    $performance_rate = floatval($_POST['performance_rate']);
    $behavior = floatval($_POST['behavior']);
    $overall = floatval($_POST['overall']);

    // Validate numeric inputs (class_activity, performance_rate, behavior, overall)
    if (!is_numeric($class_activity) || !is_numeric($performance_rate) || !is_numeric($behavior) || !is_numeric($overall)) {
        die("Invalid input for percentages.");
    }

    // Ensure percentages are within 0-100 range
    if ($class_activity < 0 || $class_activity > 100 || $performance_rate < 0 || $performance_rate > 100 || $behavior < 0 || $behavior > 100 || $overall < 0 || $overall > 100) {
        die("Percentages must be between 0 and 100.");
    }

    // Insert data into tbl_overall table
    $insertQuery = "INSERT INTO tbl_overall (roleid, fullname, attendance, assignment, performance, result, description, programming_language, class_activity, performance_rate, behavior, overall)
                    VALUES (?, (SELECT fullname FROM tbl_users WHERE roleid=?), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare and bind parameters
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssssssdddd", $roleid, $roleid, $attendance, $assignment, $performance, $result, $description, $programming_language, $class_activity, $performance_rate, $behavior, $overall);
    
    // Execute query
    if ($stmt->execute()) {
        // Success message
        // echo "Overall performance added successfully.";
        header('location: overallStd.php');
    } else {
        // Error message
        echo "Error adding overall performance: " . $stmt->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();

} else {
    // Redirect back to form page if data is not submitted properly
    header("Location: addOverall.php");
    exit();
}
