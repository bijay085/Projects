<?php
// Include your database connection details
include('D:\xampp\htdocs\glptwor\php\co.php');

// Check if ID parameter is provided in URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Prepare a delete statement
    $sql = "DELETE FROM tbl_badges WHERE badgeid = ?";

    if($stmt = $conn->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("i", $param_id);

        // Set parameters
        $param_id = trim($_GET["id"]);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Badge deleted successfully, redirect to badge index page
            header("location: badgeindex.php");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();
