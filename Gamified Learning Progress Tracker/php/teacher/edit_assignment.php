<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary
session_start();

if (!isset($_SESSION['roleid'])) {
    Header('Location: ../register/login.php');
}

$roleid = $_SESSION['roleid'];

// Check if assignment ID is provided in URL parameter
if (isset($_GET['id'])) {
    $assignment_id = $_GET['id'];

    // Query to fetch assignment details by ID
    $sql = "SELECT * FROM tbl_tworks WHERE work_id = $assignment_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $assignment_title = htmlspecialchars($row["title"]);
        $due_date = htmlspecialchars($row["due_date"]);
        $semester = htmlspecialchars($row["semester"]);
        $description = htmlspecialchars($row["description"]);

        // HTML form to edit assignment details
        echo '<!DOCTYPE html>';
        echo '<html lang="en">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Edit Assignment</title>';
        // Bootstrap CSS
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">';
        echo '</head>';
        echo '<body>';
        echo '<div class="container">';
        echo '<h2>Edit Assignment</h2>';
        echo '<form action="update_assignment.php" method="POST">';
        echo '<input type="hidden" name="assignment_id" value="' . $assignment_id . '">';
        echo '<div class="form-group">';
        echo '<label for="title">Assignment Title:</label>';
        echo '<input type="text" class="form-control" id="title" name="title" value="' . $assignment_title . '" required>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="due_date">Due Date:</label>';
        echo '<input type="date" class="form-control" id="due_date" name="due_date" value="' . $due_date . '" required>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="semester">Semester:</label>';
        echo '<select class="form-control" id="semester" name="semester" required>';
        echo '<option value="1" ' . ($semester == 1 ? 'selected' : '') . '>Semester 1</option>';
        echo '<option value="2" ' . ($semester == 2 ? 'selected' : '') . '>Semester 2</option>';
        echo '<option value="3" ' . ($semester == 3 ? 'selected' : '') . '>Semester 3</option>';
        echo '<option value="4" ' . ($semester == 4 ? 'selected' : '') . '>Semester 4</option>';
        echo '<option value="5" ' . ($semester == 5 ? 'selected' : '') . '>Semester 5</option>';
        echo '<option value="6" ' . ($semester == 6 ? 'selected' : '') . '>Semester 6</option>';
        echo '<option value="7" ' . ($semester == 7 ? 'selected' : '') . '>Semester 7</option>';
        echo '<option value="8" ' . ($semester == 8 ? 'selected' : '') . '>Semester 8</option>';
        echo '</select>';
        echo '</div>';
        echo '<div class="form-group">';
        echo '<label for="description">Description:</label>';
        echo '<textarea class="form-control" id="description" name="description" rows="4" required>' . $description . '</textarea>';
        echo '</div>';
        echo '<button type="submit" class="btn btn-primary">Update Assignment</button>';
        echo '</form>';
        echo '</div>';
        // Bootstrap JS and jQuery
        echo '<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
            integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
            crossorigin="anonymous"></script>';
        echo '<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"
            integrity="sha384-u8Hzfwze6ab3vIVj8Ft6qqXcZ9XzIWJw6u9+fq7l9I8w7S3pA1gZ0nPLZCf4nsM8"
            crossorigin="anonymous"></script>';
        echo '<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
            integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh/jFcUs7XAWL7PR9OJ3R6P/JF9TA5z4G5DlH"
            crossorigin="anonymous"></script>';
        echo '</body>';
        echo '</html>';

    } else {
        echo "Assignment not found.";
    }

} else {
    echo "Assignment ID not specified.";
}
$conn->close();
