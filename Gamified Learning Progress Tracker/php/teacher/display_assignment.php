<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; 

session_start();

if (!isset($_SESSION['roleid'])) {
    Header('Location: ../register/login.php');
}

$roleid = $_SESSION['roleid'];

// Query to fetch assignments
$sql = "SELECT * FROM tbl_tworks ORDER BY due_date DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $assignment_title = htmlspecialchars($row["title"]);
        $due_date = htmlspecialchars($row["due_date"]);
        $assignment_id = $row["work_id"];

        // HTML output for each assignment card
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">' . $assignment_title . '</h5>';
        echo '<p class="card-text">Due Date: <span class="due-date">' . $due_date . '</span></p>';
        echo '<a href="view_assignment.php?id=' . $assignment_id . '" class="card-link">View Assignment</a>';
        echo '</div></div>';
    }
} else {
    echo "No assignments found.";
}

// Close result set and database connection
$conn->close();
