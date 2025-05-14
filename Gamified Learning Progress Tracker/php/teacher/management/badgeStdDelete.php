<?php
// Include your database connection details
include('D:\xampp\htdocs\glptwor\php\co.php');

// Initialize variables to avoid PHP notices
$userid = "";
$conn_error = null;

// Check if user ID is provided via GET request
if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];

    // Retrieve user details and current badge information
    $sql = "SELECT u.userid, u.roleid, u.username, u.fullname, u.semester,
                   GROUP_CONCAT(b.badgeid) AS badge_ids, GROUP_CONCAT(b.photo) AS badge_photos
            FROM tbl_users u
            LEFT JOIN tbl_user_badges ub ON u.userid = ub.userid
            LEFT JOIN tbl_badges b ON ub.badgeid = b.badgeid
            WHERE u.userid = '$userid'
            GROUP BY u.userid";
    
    $result = $conn->query($sql);

    if ($result !== false && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Extract user and badge information
        $userid = $row['userid'];
        $roleid = $row['roleid'];
        $username = $row['username'];
        $fullname = $row['fullname'];
        $semester = $row['semester'];
        $badge_ids = explode(',', $row['badge_ids']);
        $badge_photos = explode(',', $row['badge_photos']);

        // Close result set
        $result->close();
    } else {
        // No matching user found
        echo "User not found.";
        exit();
    }
} else {
    // No user ID provided
    echo "User ID not specified.";
    exit();
}

// Handle badge deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $badgeid = $_POST['badgeid'];

    // Perform delete operation
    $delete_sql = "DELETE FROM tbl_user_badges WHERE userid = '$userid' AND badgeid = '$badgeid'";

    if ($conn->query($delete_sql) === TRUE) {
        echo "Badge deleted successfully.";
        header('location:badgeStd.php');
    } else {
        echo "Error deleting badge: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Assigned Badge</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group select {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="submit"] {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #da190b;
        }

        .badge {
            display: inline-block;
            text-align: center;
            margin-right: 5px;
        }

        .badge img {
            max-width: 50px;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .badge p {
            font-size: 10px;
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Delete Assigned Badge</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?userid=' . $userid); ?>">
            <div class="form-group">
                <label for="userid">User ID:</label>
                <input type="text" id="userid" name="userid" value="<?php echo htmlspecialchars($userid); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="fullname">Fullname:</label>
                <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="semester">Semester:</label>
                <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($semester); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="badgeid">Select Badge to Delete:</label>
                <select id="badgeid" name="badgeid" required>
                    <option value="">Select a badge</option>
                    <?php foreach ($badge_ids as $index => $badge_id): ?>
                        <option value="<?php echo htmlspecialchars($badge_id); ?>">
                            <div class="badge">
                                <img src="../badge/badgeicon/<?php echo htmlspecialchars($badge_photos[$index]); ?>" alt="Badge Photo">
                                <p><?php echo htmlspecialchars($badge_id); ?></p>
                            </div>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="submit" value="Delete Badge">
            </div>
        </form>
    </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
