<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Fetch user data from database based on userid (this part should be added if not already present)
$userid = isset($_GET['userid']) ? $_GET['userid'] : ''; // Adjust as per your logic
$sql = "SELECT * FROM tbl_users WHERE userid='$userid'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
?>

<!-- HTML form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit user</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }
        form {
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }
        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
        }
        input[type="text"],
        input[type="password"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: calc(100% - 16px);
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        select {
            width: 100%;
        }
        input[type="submit"] {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        input[type="submit"]:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
<form action="update_user.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="userid" value="<?php echo $user['userid']; ?>">
    <label for="roleid">Role ID:</label>
    <input type="text" id="roleid" name="roleid" value="<?php echo $user['roleid']; ?>" required><br><br>
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required><br><br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" value="<?php echo $user['password']; ?>" required><br><br>
    <label for="fullname">Fullname:</label>
    <input type="text" id="fullname" name="fullname" value="<?php echo $user['fullname']; ?>"><br><br>
    <label for="dob">Date of Birth:</label>
    <input type="date" id="dob" name="dob" value="<?php echo $user['dob']; ?>"><br><br>
    <label for="gender">Gender:</label>
    <select id="gender" name="gender">
        <option value="male" <?php if ($user['gender'] == 'male') echo 'selected'; ?>>Male</option>
        <option value="female" <?php if ($user['gender'] == 'female') echo 'selected'; ?>>Female</option>
        <option value="other" <?php if ($user['gender'] == 'other') echo 'selected'; ?>>Other</option>
    </select><br><br>
    <label for="address">Address:</label>
    <input type="text" id="address" name="address" value="<?php echo $user['address']; ?>"><br><br>
    <label for="phone">Phone:</label>
    <input type="text" id="phone" name="phone" value="<?php echo $user['phone']; ?>"><br><br>
    <label for="program">Program:</label>
    <input type="text" id="program" name="program" value="<?php echo $user['program']; ?>"><br><br>
    <label for="semester">Semester:</label>
    <input type="number" id="semester" name="semester" value="<?php echo $user['semester']; ?>"><br><br>
    <label for="admission_date">Admission Date:</label>
    <input type="date" id="admission_date" name="admission_date" value="<?php echo $user['admission_date']; ?>"><br><br>
    <label for="photo">Photo:</label>
    <input type="file" id="photo" name="photo"><br><br>
    <input type="submit" value="Update">
</form>
</body>
</html>
