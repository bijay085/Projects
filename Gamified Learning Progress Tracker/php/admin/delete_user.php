<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Check if userid is provided and valid
if (isset($_GET['userid']) && !empty($_GET['userid'])) {
    $userid = $_GET['userid'];

    // Prepare SQL statement to delete user from tbl_users
    $sql = "DELETE FROM tbl_users WHERE userid = ?";

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $userid);

    // Execute the statement
    if ($stmt->execute()) {
        // Delete successful
        echo "User with ID $userid has been deleted successfully.";
        header("Location: adminindex.php");
    } else {
        // Delete failed
        echo "Error deleting user: " . $conn->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect to user management page if userid is not provided
    header("Location: adminindex.php");
    exit();
}
