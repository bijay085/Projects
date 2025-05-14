<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; 

session_start();

if (!isset($_SESSION['roleid'])) {
    Header('Location: ../register/login.php');
}

$roleid = $_SESSION['roleid'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user inputs for security
    $assignment_id = $_POST['assignment_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    // Update assignment query
    $sql = "UPDATE tbl_tworks SET title='$title', due_date='$due_date', semester='$semester', description='$description' WHERE work_id='$assignment_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Assignment updated successfully";
        header('Location: teacherindex.php');

    } else {
        echo "Error updating assignment: " . $conn->error;
    }
}

$conn->close();
