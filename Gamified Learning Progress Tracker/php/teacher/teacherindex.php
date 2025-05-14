<?php
session_start();
include_once 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary

// Redirect to login if roleid is not set in session
if (!isset($_SESSION['roleid'])) {
    header('Location: ../register/login.php');
    exit(); // Ensure script stops here
}

$roleid = $_SESSION['roleid'];

// Mapping for semester selection
$semester_map = array(
    'semester1' => 1,
    'semester2' => 2,
    'semester3' => 3,
    'semester4' => 4,
    'semester5' => 5,
    'semester6' => 6,
    'semester7' => 7,
    'semester8' => 8,
);

// Constructing SQL query based on filters
$sql = "SELECT * FROM tbl_tworks WHERE 1=1"; // Always true condition to start WHERE clause safely

if (isset($_GET['semester']) && $_GET['semester'] !== 'all') {
    $selected_semester = $_GET['semester'];
    if (array_key_exists($selected_semester, $semester_map)) {
        $numeric_semester = $semester_map[$selected_semester];
        $sql .= " AND semester = '$numeric_semester'";
    } else {
        echo "Invalid semester filter.";
        exit;
    }
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql .= " AND title LIKE '%$search_query%'";
}

$sql .= " ORDER BY due_date DESC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
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

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .assignment-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .assignment-card:hover {
            transform: scale(1.02);
        }

        .assignment-card h5 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .assignment-card p {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .assignment-card .due-date {
            font-weight: bold;
        }

        .assignment-card .semester {
            font-style: italic;
        }

        .filter-form {
            background-color: #e8f0fe;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filter-form label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        .filter-form .form-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
        }

        .filter-form .form-group {
            flex: 1 1 45%;
            margin-bottom: 20px;
        }

        .filter-form .form-group input,
        .filter-form .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 16px;
            margin-top: 8px;
            background-color: #fff;
            color: #333;
            transition: border-color 0.3s ease;
        }

        .filter-form .form-group input:focus,
        .filter-form .form-group select:focus {
            border-color: #007bff;
        }

        .filter-form .form-group button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 8px;
            transition: background-color 0.3s ease;
        }

        .filter-form .form-group button:hover {
            background-color: #0056b3;
        }

        .filter-form .clear-btn {
            background-color: #dc3545;
        }

        .filter-form .clear-btn:hover {
            background-color: #c82333;
        }

        .btn-block {
            margin-top: 28px;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a class="navbar-brand" href="teacherindex.php">Teacher Dashboard</a>
        <div class="navbar-links">
            <a class="nav-link active" href="teacherindex.php">Home</a>
            <a class="nav-link" href="submit_assignment.php">Post Assignment</a>
            <a class="nav-link" href="./overall/overallindex.php">Overall index</a>
            <a class="nav-link" href="../logout.php">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Homework Assignments</h2>
        <!-- Filter by Semester and Search -->
        <form method="GET" class="filter-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="semester">Filter by Semester:</label>
                    <select class="form-control" id="semester" name="semester">
                        <option value="all">All Semesters</option>
                        <option value="semester1">Semester 1</option>
                        <option value="semester2">Semester 2</option>
                        <option value="semester3">Semester 3</option>
                        <option value="semester4">Semester 4</option>
                        <option value="semester5">Semester 5</option>
                        <option value="semester6">Semester 6</option>
                        <option value="semester7">Semester 7</option>
                        <option value="semester8">Semester 8</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="search">Search by Assignment Title:</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Enter keywords">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Filter & Search</button>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-primary btn-block clear-btn" onclick="clearFilters()">Clear
                        Filters</button>
                </div>
            </div>
        </form>

        <hr>

        <div id="assignments-list">
            <!-- PHP script to fetch and display assignments -->
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $assignment_title = htmlspecialchars($row["title"]);
                    $due_date = htmlspecialchars($row["due_date"]);
                    $semester = htmlspecialchars($row["semester"]);
                    $description = htmlspecialchars($row["description"]);
                    $assignment_id = $row["work_id"];

                    // Calculate remaining time
                    $now = new DateTime();
                    $due_date_obj = new DateTime($due_date);
                    $interval = $now->diff($due_date_obj);

                    if ($now > $due_date_obj) {
                        $remaining_time = '<span class="text-danger">Due date over</span>';
                    } else {
                        $remaining_time = '<span class="text-danger">(' . $interval->days . ' days ' . $interval->h . ' hours ' . $interval->i . ' minutes left)</span>';
                    }

                    echo '<div class="card assignment-card">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $assignment_title . '</h5>';
                    echo '<p class="card-text">Due Date: <span class="due-date">' . $due_date . '</span> ' . $remaining_time . '</p>';
                    echo '<p class="card-text">Semester: <span class="semester">' . $semester . '</span></p>';
                    echo '<p class="card-text">' . $description . '</p>';
                    echo '<a href="edit_assignment.php?id=' . $assignment_id . '" class="card-link">Edit Assignment</a>';
                    echo '</div></div>';
                }
            } else {
                echo "<p>No assignments found.</p>";
            }

            $conn->close();
            ?>
        </div>
    </div>

    <script>
        function clearFilters() {
            document.getElementById('semester').value = 'all';
            document.getElementById('search').value = '';
            // Submit the form to apply clear filters
            document.querySelector('.filter-form').submit();
        }

        setInterval(updateTime, 60000);

        function updateTime() {
            var dueDates = document.getElementsByClassName("due-date");
            for (var i = 0; i < dueDates.length; i++) {
                var dueDate = new Date(dueDates[i].innerText);
                var now = new Date();
                var diff = dueDate.getTime() - now.getTime();
                var days = Math.floor(diff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((diff % (1000 * 60)) / (1000 * 60));

                var timerElement = dueDates[i].nextElementSibling;
                if (diff <= 0) {
                    timerElement.innerHTML = '<span class="text-danger">Due date over</span>';
                } else {
                    var timeLeft = "(" + days + " days " + hours + " hours " + minutes + " minutes left)";
                    timerElement.innerHTML = '<span class="text-danger">' + timeLeft + '</span>';
                }
            }
        }
    </script>
</body>

</html>
