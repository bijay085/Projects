<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary

// Function to sanitize input data
function sanitize($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Initialize variables
$roleid = '';
$roletype = '';

// Check if roleid parameter is provided via GET
if (isset($_GET['roleid'])) {
    $roleid = sanitize($_GET['roleid']);

    // Fetch role details from database
    $sql = "SELECT * FROM tbl_roles WHERE roleid = '$roleid'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $roletype = $row['roletype'];
    } else {
        echo "Role not found.";
        exit;
    }
} else {
    echo "Role ID not provided.";
    exit;
}

// Handle form submission for updating role
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $new_roleid = sanitize($_POST['roleid']);
    $roletype = sanitize($_POST['roletype']);

    // Check if new roleid is different from current roleid
    if ($new_roleid !== $roleid) {
        // Update roleid in dependent tables if necessary (handle this according to your application logic)
        // Example: You may need to update tbl_tworks if roleid is used as a foreign key there.

        // Proceed with updating roleid in tbl_roles
        $update_sql = "UPDATE tbl_roles SET roleid = '$new_roleid', roletype = '$roletype' WHERE roleid = '$roleid'";
    } else {
        // Update only roletype if roleid remains the same
        $update_sql = "UPDATE tbl_roles SET roletype = '$roletype' WHERE roleid = '$roleid'";
    }

    if ($conn->query($update_sql) === TRUE) {
        // Redirect back to roleassign.php after successful update
        header("Location: roleassign.php");
        exit;
    } else {
        echo "Error updating role: " . $conn->error;
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role</title>
    <style>
        /* Your CSS styles */
    </style>
</head>

<body>
    <h3>Edit Role</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?roleid=" . urlencode($roleid); ?>">
        Current Role ID: <strong><?php echo htmlspecialchars($roleid); ?></strong><br><br>
        New Role ID: <input type="text" name="roleid" value="<?php echo htmlspecialchars($roleid); ?>"><br><br>
        Role Type:
        <select name="roletype">
            <option value="student" <?php echo ($roletype == 'student') ? 'selected' : ''; ?>>Student</option>
            <option value="teacher" <?php echo ($roletype == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
        </select><br><br>
        <input type="submit" value="Update Role">
    </form>
</body>

</html>
