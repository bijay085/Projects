<?php
// Include your database connection details
include('D:\xampp\htdocs\glptwor\php\co.php');

// Initialize variables to avoid PHP notices
$result = null;
$conn_error = null;
$totalUsersWithBadges = 0;
$filterWithBadges = false;

// Check connection
if ($conn->connect_error) {
    $conn_error = "Connection failed: " . $conn->connect_error;
} else {
    // Define initial SQL query
    $sql = "SELECT u.userid, u.roleid, u.username, u.fullname, u.semester, 
                   GROUP_CONCAT(b.photo) AS badge_photos, GROUP_CONCAT(b.name) AS badge_names
            FROM tbl_users u
            LEFT JOIN tbl_user_badges ub ON u.userid = ub.userid
            LEFT JOIN tbl_badges b ON ub.badgeid = b.badgeid";

    // Check if search form is submitted
    if (isset($_POST['search'])) {
        $searchTerm = $_POST['searchTerm'];
        $badgeSearchTerm = $_POST['badgeSearchTerm'];
        $roleFilter = $_POST['roleFilter'];
        $filterWithBadges = isset($_POST['filterWithBadges']);
        
        // Adjust SQL query to filter based on user input and badge name
        $conditions = [];
        
        if (!empty($searchTerm)) {
            $conditions[] = "(u.username LIKE '%$searchTerm%' OR u.fullname LIKE '%$searchTerm%' OR u.semester LIKE '%$searchTerm%' OR u.roleid LIKE '%$searchTerm%')";
        }
        
        if (!empty($badgeSearchTerm)) {
            $conditions[] = "b.name LIKE '%$badgeSearchTerm%'";
        }

        if (!empty($roleFilter)) {
            if ($roleFilter == 'student') {
                $conditions[] = "u.roleid LIKE 's%'";
            } else if ($roleFilter == 'teacher') {
                $conditions[] = "u.roleid LIKE 't%'";
            }
        }

        if ($filterWithBadges) {
            $conditions[] = "b.badgeid IS NOT NULL";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
    }

    // Group results by user
    $sql .= " GROUP BY u.userid";

    // Execute SQL query
    $result = $conn->query($sql);

    // Get total users with badges if filter is applied
    if ($filterWithBadges) {
        $countSql = "SELECT COUNT(DISTINCT u.userid) AS total
                     FROM tbl_users u
                     LEFT JOIN tbl_user_badges ub ON u.userid = ub.userid
                     LEFT JOIN tbl_badges b ON ub.badgeid = b.badgeid
                     WHERE b.badgeid IS NOT NULL";
        $countResult = $conn->query($countSql);
        if ($countResult !== false && $countResult->num_rows > 0) {
            $totalUsersWithBadges = $countResult->fetch_assoc()['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Badge Information</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }

        .student-table-container {
            margin: 20px auto;
            width: 95%;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .student-table thead {
            background-color: #f2f2f2;
        }

        .student-table th,
        .student-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ccc;
        }

        .student-table img {
            max-width: 50px;
            height: auto;
            display: block;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .student-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .navbar {
            display: flex;
            justify-content: right;
            align-items: center;
            background-color: #363838;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(57, 94, 105, 0.4);
            border-radius: 0 0 7px 7px;
            margin-bottom: 20px;
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

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            padding: 8px;
            font-size: 16px;
            margin-right: 10px;
        }

        .search-form select {
            padding: 8px;
            font-size: 16px;
            margin-right: 10px;
        }

        .search-form input[type="submit"],
        .search-form input[type="button"],
        .search-form input[type="checkbox"] {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .search-form input[type="button"] {
            background-color: #f44336;
        }

        .search-form input[type="submit"]:hover {
            background-color: #45a049;
        }

        .search-form input[type="button"]:hover {
            background-color: #d32f2f;
        }

        .search-form input[type="checkbox"] {
            margin-right: 5px;
        }

        .edit-btn,
        .delete-btn {
            padding: 6px 12px;
            background-color: #008CBA;
            color: white;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            border-radius: 3px;
        }

        .edit-btn:hover,
        .delete-btn:hover {
            background-color: #006080;
        }

        .total-users-with-badges {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="navbar">
        
        <a href="../admin/adminindex.php" style="text-align: left;">Admin Dashboard</a>
        <a href="badgeindex.php">Badge Index</a>
        <a href="badgeadd.php">Add New Badge</a>
        <!-- <a href="badgeassign.php">Assign Badge</a> -->
        <a href="badgeStd.php" class="active">Student Badge</a>
    </div>

    <div class="student-table-container">
        <div class="search-form">
            <form method="POST" action="">
                <input type="text" name="searchTerm" placeholder="Search by Username, Fullname, Semester...">
                <input type="text" name="badgeSearchTerm" placeholder="Search by Badge Name...">
                <select name="roleFilter">
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
                <input type="checkbox" name="filterWithBadges" id="filterWithBadges" <?php if ($filterWithBadges) echo 'checked'; ?>>
                <label for="filterWithBadges">Only users with badges</label>
                <input type="submit" name="search" value="Search">
                <input type="button" value="Clear Search and Filter" onclick="window.location.href='badgeStd.php'">
            </form>
        </div>

        <?php
        // Check for connection error
        if ($conn_error !== null) {
            echo "<p>$conn_error</p>";
        } else {
            // Display total users with badges if the filter is applied
            if ($filterWithBadges) {
                echo "<div class='total-users-with-badges'><strong>Total users with badges:</strong> $totalUsersWithBadges</div>";
            }

            // Check if data was found
            if ($result !== false && $result->num_rows > 0) {
                echo "<table class='student-table'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>User ID</th>";
                echo "<th>Role ID</th>";
                echo "<th>Username</th>";
                echo "<th>Fullname</th>";
                echo "<th>Semester</th>";
                echo "<th>Badges</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["userid"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["roleid"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["username"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["fullname"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["semester"]) . "</td>";
                    echo "<td>";

                    if (!empty($row["badge_photos"])) {
                        $badgePhotos = explode(',', $row["badge_photos"]);
                        $badgeNames = explode(',', $row["badge_names"]);
                        foreach ($badgePhotos as $index => $badgePhoto) {
                            echo "<div style='display: inline-block; text-align: center; margin-right: 5px;'>
                                    <img src='../badge/badgeicon/" . htmlspecialchars($badgePhoto) . "' alt='" . htmlspecialchars($badgeNames[$index]) . "' title='" . htmlspecialchars($badgeNames[$index]) . "'>
                                    <p style='font-size: 10px;'>" . htmlspecialchars($badgeNames[$index]) . "</p>
                                  </div>";
                        }
                    } else {
                        echo $row["roleid"][0] == 't' ? "No badge assigned (Teacher)" : "No badge assigned";
                    }

                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No data found.</p>";
            }
        }

        // Close database connection
        $conn->close();
        ?>
    </div>
</body>

</html>
