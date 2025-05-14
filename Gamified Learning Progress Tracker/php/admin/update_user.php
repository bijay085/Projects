<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Initialize variables with empty strings to avoid undefined array key warnings
$userid = isset($_POST['userid']) ? $_POST['userid'] : '';
$roleid = isset($_POST['roleid']) ? $_POST['roleid'] : '';
$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
$dob = isset($_POST['dob']) ? $_POST['dob'] : '';
$gender = isset($_POST['gender']) ? $_POST['gender'] : '';
$address = isset($_POST['address']) ? $_POST['address'] : '';
$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
$program = isset($_POST['program']) ? $_POST['program'] : '';
$semester = isset($_POST['semester']) ? $_POST['semester'] : '';
$admission_date = isset($_POST['admission_date']) ? $_POST['admission_date'] : '';
$photo_name = '';

// Check if a new photo is uploaded
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $photo = $_FILES['photo'];

    // Validate the photo (e.g., check file size and type)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($photo['type'], $allowed_types)) {
        echo "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        exit;
    }

    if ($photo['size'] > 2000000) { // 2MB file size limit
        echo "File size exceeds 2MB limit.";
        exit;
    }

    // Move the uploaded file to a designated folder
    $upload_dir = 'D:\xampp\htdocs\glptwor\uploads\\';
    $photo_name = basename($photo['name']);
    $target_file = $upload_dir . $photo_name;

    if (!move_uploaded_file($photo['tmp_name'], $target_file)) {
        echo "Error uploading file.";
        exit;
    }
}

// Prepare and execute the SQL update statement
$sql = "UPDATE tbl_users SET
            roleid='$roleid',
            username='$username',
            password='$password',
            fullname='$fullname',
            dob='$dob',
            gender='$gender',
            address='$address',
            phone='$phone',
            program='$program',
            semester='$semester',
            admission_date='$admission_date'";

if ($photo_name != '') {
    $sql .= ", photo='$photo_name'";
}

$sql .= " WHERE userid='$userid'";

if ($conn->query($sql) === TRUE) {
    echo "User updated successfully.";
    header('location: adminindex.php');
} else {
    echo "Error updating user: " . $conn->error;
}

// Close the database connection
$conn->close();
