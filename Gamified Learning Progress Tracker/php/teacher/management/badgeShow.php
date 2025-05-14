<?php
// Include your database connection details
include ('D:\xampp\htdocs\glptwor\php\co.php');

// Initialize variables to avoid PHP notices
$result = null;
$conn_error = null;

// Check connection
if ($conn->connect_error) {
    $conn_error = "Connection failed: " . $conn->connect_error;
} else {
    // Query to fetch all badges
    $sql = "SELECT * FROM tbl_badges";
    $result = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Index</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        /* Container for the table */
        .badge-table-container {
            margin: 20px auto;
            width: 95%;
        }

        /* Table styles */
        .badge-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        /* Header row styles */
        .badge-table thead {
            background-color: #f2f2f2;
        }

        /* Table header and cell styles */
        .badge-table th,
        .badge-table td {
            padding: 8px 20px;
            text-align: left;
            border: 1px solid #ccc;
        }

        /* Image styles */
        .badge-table img {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        /* Row hover effect */
        .badge-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Specific styles for each column */
        .badge-table .badge-id {
            width: 10%;
            font-weight: bold;
        }

        .badge-table .badge-name {
            width: 20%;
        }

        .badge-table .badge-photo {
            width: 15%;
        }

        .badge-table .badge-description {
            width: 55%;
        }

        .badge-table .badge-actions {
            width: 20%;
        }

        .badge-table .badge-actions a {
            display: inline-block;
            margin-right: 10px;
            text-decoration: none;
            color: #333;
        }

        .badge-table .badge-actions a:hover {
            color: #000;
        }


        .navbar {
            display: flex;
            justify-content: right;
            align-items: center;
            background-color: #363838;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(57, 94, 105, 0.4);
            border-radius: 0 0 7px 7px;
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

        /* Styles specifically for action buttons */
        .badge-table .badge-actions {
            width: 20%;
        }

        .badge-table .badge-actions a {
            display: inline-block;
            padding: 8px 12px;
            /* Padding around the button text */
            margin-right: 5px;
            /* Space between buttons */
            text-decoration: none;
            color: #333;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }

        .badge-table .badge-actions a:hover {
            background-color: #f0f0f0;
            color: #000;
            border-color: #999;
        }
    </style>
</head>

<body>
<div class="navbar">
        <a href="badgeindex.php">Badge Index</a>
        <a href="badgeadd.php" class="active">Add New Badge</a>
        <a href="badgeassign.php">Assign Badge</a>
        <a href="badgeStd.php">Student Badge</a>
    </div>

    <div class='badge-table-container'>

        <?php
        // Check for connection error
        if ($conn_error !== null) {
            echo "<p>$conn_error</p>";
        } else {
            // Check if badges were found
            if ($result !== false && $result->num_rows > 0) {
                echo "<table class='badge-table'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Badge ID</th>";
                echo "<th>Name</th>";
                echo "<th>Photo</th>";
                echo "<th>Description</th>";
                echo "<th>Actions</th>"; // New column for actions
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";

                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='badge-row'>";
                    echo "<td class='badge-id'>" . htmlspecialchars($row["badgeid"]) . "</td>";
                    echo "<td class='badge-name'>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td class='badge-photo'><img src='../../badge/badgeicon/" . htmlspecialchars($row["photo"]) . "' alt='Badge Photo'></td>";
                    echo "<td class='badge-description'>" . htmlspecialchars($row["description"]) . "</td>";
                    echo "<td class='badge-actions'>";
                    echo "<a href='badgeedit.php?id=" . htmlspecialchars($row["badgeid"]) . "'>Edit</a>";
                    echo "<a href='badgedelete.php?id=" . htmlspecialchars($row["badgeid"]) . "'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
            } else {
                echo "<p>No badges found.</p>";
            }
        }

        // Close database connection
        $conn->close();
        ?>

    </div>

</body>

</html>