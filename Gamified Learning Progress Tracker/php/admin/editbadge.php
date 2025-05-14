<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    die("Session not set. Please log in.");
}

$roleid = $_SESSION['roleid'];

// Check if the user is an admin (assuming admin has roleid 'admin')
$isAdmin = $_SESSION['roleid'] === 'admin';

$query = "SELECT u.*, b.photo AS badge_photo, b.name AS badge_name FROM tbl_users u
          LEFT JOIN tbl_badges b ON u.badgeid = b.badgeid
          WHERE u.roleid = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("s", $roleid); // Binding as string

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$userResult = $result->fetch_assoc();

// Debugging output
if (!$userResult) {
    echo "No user found with role ID: " . htmlspecialchars($roleid);
    die();
}

// Update user profile if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $program = $_POST['program'];
    $semester = $_POST['semester'];
    $admission_date = $_POST['admission_date'];

    $updateQuery = "UPDATE tbl_users SET fullname = ?, username = ?, program = ?, semester = ?, admission_date = ? WHERE roleid = ?";
    $updateStmt = $conn->prepare($updateQuery);

    if ($updateStmt === false) {
        die("Error preparing update query: " . $conn->error);
    }

    $updateStmt->bind_param("ssssis", $fullname, $username, $program, $semester, $admission_date, $roleid);

    if (!$updateStmt->execute()) {
        die("Error executing update query: " . $updateStmt->error);
    }

    // Refresh user data after update
    $stmt->execute();
    $userResult = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
         body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .navbar {
            width: 100%;
            background-color: #333;
            padding: 1px 4px;
            box-shadow: 0 2px 4px rgba(57, 94, 105, 0.4);
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: fixed;
            border-radius: 0 0 7px 7px;
            top: 0;
            z-index: 1000;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            text-align: center;
            border-radius: 4px;
            position: relative;
        }

        .navbar a:after {
            content: '';
            display: block;
            width: 0;
            height: 3px;
            background: none;
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.3) 50%, transparent 50%);
            background-size: 6px 3px;
            position: absolute;
            bottom: -3px;
            left: 0;
            transition: width 0.3s;
        }

        .navbar a.active:after {
            width: 100%;
            animation: moveDots 1s linear infinite;
        }

        @keyframes moveDots {
            from {
                background-position: 0 0;
            }

            to {
                background-position: 6px 0;
            }
        }

        .navbar a:hover {
            background-color: #ffffff;
            color: rgb(85, 134, 146);
        }

        .profile-container {
            max-width: 900px;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 150px;
            /* To account for the fixed navbar */
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .badges-container {
            margin-top: 20px;
            width: 100%;
            text-align: center;
        }

        .badge {
            display: inline-block;
            position: relative;
            background-color: #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin: 10px;
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge:hover::after {
            content: attr(data-name);
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            white-space: nowrap;
        }

        .info-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            margin-top: 20px;
            width: 100%;
        }

        .info-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: calc(50% - 10px);
            text-align: center;
            margin-bottom: 20px;
            /* Add margin to ensure space between rows */
        }

        .user-info h2,
        .section-title {
            margin: 10px 0;
            color: #333;
        }

        .user-info p {
            margin: 5px 0;
            color: #666;
        }

        .skill-bar {
            margin: 10px 0;
            width: 100%;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
        }

        .skill-bar-inner {
            height: 20px;
            width: 70%;
            /* Example percentage, replace with dynamic data */
            background-color: #4caf50;
            border-radius: 5px;
            text-align: right;
            padding-right: 10px;
            color: white;
            line-height: 20px;
            /* Center text vertically */
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
        <img src="<?php echo htmlspecialchars($userResult['photo']); ?>" alt="Profile Picture" class="profile-picture">

        <div class="badges-container">
            <?php if (!empty($userResult['badge_photo'])): ?>
                <div class="badge" data-name="<?php echo htmlspecialchars($userResult['badge_name']); ?>">
                    <img src="<?php echo htmlspecialchars($userResult['badge_photo']); ?>"
                        alt="<?php echo htmlspecialchars($userResult['badge_name']); ?>">
                </div>
            <?php else: ?>
                <p>No badges assigned.</p>
            <?php endif; ?>
        </div>

        <div class="info-grid">
            <?php if ($isAdmin): ?>
                <form method="post" class="info-container user-info">
                    <h2>Edit Profile</h2>
                    <p><strong>Full Name:</strong> <input type="text" name="fullname" value="<?php echo htmlspecialchars($userResult['fullname']); ?>"></p>
                    <p><strong>Username:</strong> <input type="text" name="username" value="<?php echo htmlspecialchars($userResult['username']); ?>"></p>
                    <p><strong>Program:</strong> <input type="text" name="program" value="<?php echo htmlspecialchars($userResult['program']); ?>"></p>
                    <p><strong>Semester:</strong> <input type="text" name="semester" value="<?php echo htmlspecialchars($userResult['semester']); ?>"></p>
                    <p><strong>Admission Date:</strong> <input type="date" name="admission_date" value="<?php echo htmlspecialchars($userResult['admission_date']); ?>"></p>
                    <button type="submit">Update</button>
                </form>
            <?php else: ?>
                <div class="info-container user-info">
                    <h2><?php echo htmlspecialchars($userResult['fullname']); ?></h2>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($userResult['username']); ?></p>
                    <p><strong>Program:</strong> <?php echo htmlspecialchars($userResult['program']); ?></p>
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($userResult['semester']); ?></p>
                    <p><strong>Admission Date:</strong> <?php echo htmlspecialchars($userResult['admission_date']); ?></p>
                </div>
            <?php endif; ?>

            <div class="info-container">
                <h2 class="section-title">Result</h2>
                <p>Your recent results will be displayed here.</p>
            </div>

            <div class="info-container">
                <h2 class="section-title">Description</h2>
                <p>A brief description about the user will be shown here.</p>
            </div>

            <div class="info-container">
                <h2 class="section-title">Skill</h2>
                <div class="skill-bar">
                    <div class="skill-bar-inner" style="width: 80%;">80%</div> <!-- Example skill percentage -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>
