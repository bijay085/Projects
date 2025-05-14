<?php
$uploadsPath = '../../uploads/';
include 'D:/xampp/htdocs/glptwor/php/co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    header('Location: ../register/login.php');
    exit;
}

$roleid = $_SESSION['roleid'];
$query = "SELECT * FROM tbl_users WHERE roleid = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("s", $roleid);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "No user found with role ID: " . htmlspecialchars($roleid);
    die();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $program = $_POST['program'];
    $semester = $_POST['semester'];
    $admission_date = $_POST['admission_date'];
    
    $photo = $_FILES['photo']['name'];
    $photo_tmp = $_FILES['photo']['tmp_name'];

    if ($photo) {
        move_uploaded_file($photo_tmp, $uploadsPath . $photo);
        $query = "UPDATE tbl_users SET fullname=?, username=?, program=?, semester=?, admission_date=?, photo=? WHERE roleid=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $fullname, $username, $program, $semester, $admission_date, $photo, $roleid);
    } else {
        $query = "UPDATE tbl_users SET fullname=?, username=?, program=?, semester=?, admission_date=? WHERE roleid=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $fullname, $username, $program, $semester, $admission_date, $roleid);
    }

    if ($stmt->execute()) {
        header("Location: profile.php");
        exit;
    } else {
        echo "Error updating record: " . $stmt->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.9)), url('profile.webp');
            background-size: cover;
            background-repeat: no-repeat;
            color: #fff;
            z-index: 1;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 7px 7px;
            background-color: #333;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(57, 94, 105, 0.4);
            z-index: 2;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            text-align: center;
            border-radius: 4px;
            position: relative;
        }

        .navbar a:hover {
            background-color: #ffffff;
            color: rgb(85, 134, 146);
        }

        .profile-container {
            max-width: 800px;
            background-color: rgba(155, 174, 212, 0.9);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 40px auto;
            color: #333;
            z-index: 1;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            position: relative;
            border-radius: 50%;
        }

        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        form {
            width: 100%;
            max-width: 500px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form input[type="text"],
        form input[type="file"],
        form input[type="date"],
        form button {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form button {
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #45a049;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #333;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #4caf50;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="stdindex.php">Home</a>
        <a href="studentDisplayAssignment.php">Assignment</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-img">
                <img src="<?php echo $uploadsPath . htmlspecialchars($user['photo']); ?>" alt="Profile Picture">
            </div>
            <h2>Edit Profile</h2>
        </div>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" placeholder="Full Name" required>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" placeholder="Username" required>
            <input type="text" name="program" value="<?php echo htmlspecialchars($user['program']); ?>" placeholder="Program" required>
            <input type="text" name="semester" value="<?php echo htmlspecialchars($user['semester']); ?>" placeholder="Semester" required>
            <input type="date" name="admission_date" value="<?php echo htmlspecialchars($user['admission_date']); ?>" placeholder="Admission Date" required>
            <input type="file" name="photo">
            <button type="submit">Save Changes</button>
        </form>
        <div class="back-link">
            <a href="profile.php">Back to Profile</a>
        </div>
    </div>
</body>

</html>
