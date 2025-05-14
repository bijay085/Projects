<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary

// Function to sanitize input data
function sanitize($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Check if roleid parameter is provided via GET
if (isset($_GET['roleid'])) {
    $roleid = sanitize($_GET['roleid']);

    // Check if there are dependent records in tbl_tworks
    $check_sql = "SELECT COUNT(*) as count FROM tbl_tworks WHERE roleid = '$roleid'";
    $check_result = $conn->query($check_sql);
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        // Handle dependent records (e.g., update or delete)
        $update_sql = "UPDATE tbl_tworks SET roleid = NULL WHERE roleid = '$roleid'";
        if ($conn->query($update_sql) === TRUE) {
            // Proceed with deleting the role from tbl_roles
            $delete_sql = "DELETE FROM tbl_roles WHERE roleid = '$roleid'";
            if ($conn->query($delete_sql) === TRUE) {
                // Redirect back to roleassign.php after successful deletion
                header("Location: roleassign.php");
                exit;
            } else {
                echo "Error deleting role: " . $conn->error;
            }
        } else {
            echo "Error updating dependent records: " . $conn->error;
        }
    } else {
        // No dependent records, proceed with deletion
        $delete_sql = "DELETE FROM tbl_roles WHERE roleid = '$roleid'";
        if ($conn->query($delete_sql) === TRUE) {
            // Redirect back to roleassign.php after successful deletion
            header("Location: roleassign.php");
            exit;
        } else {
            echo "Error deleting role: " . $conn->error;
        }
    }
} else {
    echo "Role ID not provided.";
}

// Close database connection
$conn->close();
