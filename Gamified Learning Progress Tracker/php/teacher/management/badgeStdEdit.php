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

// Handle form submission for updating badge photos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $selected_badges = $_POST['badgeids'];

    // First, delete existing badge assignments for the user
    $delete_sql = "DELETE FROM tbl_user_badges WHERE userid = '$userid'";
    $conn->query($delete_sql);

    // Then, insert new badge assignments
    $insert_sql = "INSERT INTO tbl_user_badges (userid, badgeid) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    foreach ($selected_badges as $badgeid) {
        $stmt->bind_param("ss", $userid, $badgeid);
        $stmt->execute();
    }

    echo "Badges updated successfully.";
    header('location:badgeStd.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Badges</title>
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
        .form-group select,
        .form-group input[type="checkbox"] {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
        }

        .badge-list {
            display: flex;
            flex-wrap: wrap;
        }

        .badge-item {
            display: flex;
            align-items: center;
            margin-right: 10px;
            margin-bottom: 10px;
        }

        .badge-item img {
            max-width: 50px;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit User Badges</h2>
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
                <label for="badges">Select Badges:</label>
                <div class="badge-list">
                    <?php
                    // Fetch all badges from tbl_badges
                    $badge_sql = "SELECT badgeid, name, photo FROM tbl_badges";
                    $badge_result = $conn->query($badge_sql);

                    if ($badge_result !== false && $badge_result->num_rows > 0) {
                        while ($badge_row = $badge_result->fetch_assoc()) {
                            $checked = in_array($badge_row['badgeid'], $badge_ids) ? 'checked' : '';
                            echo "<div class='badge-item'>
                                    <input type='checkbox' id='badge_{$badge_row['badgeid']}' name='badgeids[]' value='" . htmlspecialchars($badge_row['badgeid']) . "' $checked>
                                    <label for='badge_{$badge_row['badgeid']}'>
                                        <img src='../../badge/badgeicon/" . htmlspecialchars($badge_row['photo']) . "' alt='" . htmlspecialchars($badge_row['name']) . "'>
                                        " . htmlspecialchars($badge_row['name']) . "
                                    </label>
                                  </div>";
                        }
                        $badge_result->close();
                    } else {
                        echo "<p>No badges found</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <input type="submit" value="Update Badges">
            </div>
        </form>
    </div>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
