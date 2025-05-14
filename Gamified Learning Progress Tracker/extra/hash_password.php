<?php
include ('D:\xampp\htdocs\glptwor\php\co.php');

// Fetch all users
$sql = "SELECT userid, password FROM tbl_users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $userid = $row['userid'];
        $password = $row['password'];
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update the user's password with the hashed password
        $updateSql = "UPDATE tbl_users SET password='$hashedPassword' WHERE userid='$userid'";
        if ($conn->query($updateSql) === TRUE) {
            echo "Password updated for user ID: $userid<br>";
        } else {
            echo "Error updating password for user ID: $userid - " . $conn->error . "<br>";
        }
    }
} else {
    echo "No users found.";
}

$conn->close();
