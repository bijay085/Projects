<?php
include 'D:\xampp\htdocs\glptwor\php\co.php'; 

session_start();
if (!isset($_SESSION['roleid'])) {
    header('Location: ../register/login.php');
    exit(); // Ensure script stops here if session is not set
}

$roleid = $_SESSION['roleid']; // Retrieve roleid from session

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $program = $_POST['program'];
    $semester = $_POST['semester'];

    // Insert assignment data into database
    $sql = "INSERT INTO tbl_tworks (roleid, title, description, due_date, post_date, program, semester)
            VALUES ('$roleid', '$title', '$description', '$due_date', NOW(), '$program', '$semester')";

    if ($conn->query($sql) === TRUE) {
        header('Location: teacherindex.php'); 
        exit; // Ensure that no further code is executed after redirection
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close database connection
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Assignment</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        integrity="sha512-JHRvDQw+MNSo0V48B/J3m7uEFSj0jkaHq9Iw9J9zUeHhsb7r9NpBxPnJz+gJNZc4H5avthXrVJu1M4uNoPvGfw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .btn-primary,
        .btn-secondary {
            display: inline-block;
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            transition: background-color 0.3s;
            text-align: center;
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
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
    </style>
</head>

<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#">Teacher Dashboard</a>
        <div class="navbar-links">
            <a class="nav-link" href="teacherindex.php">Home</a>
            <a class="nav-link active" href="submit_assignement.php">Post Assignment</a>
            <a class="nav-link" href="./overall/overallindex.php">Overall index</a>
            <a class="nav-link" href="../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container mt-4">
        <h2>Post New Assignment</h2>
        <form action="#" method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label for="title">Assignment Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Assignment Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="due_date">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="form-group">
                <label for="program">Program</label>
                <select class="form-control" id="program" name="program" required>
                    <option value="BCA">BCA</option>
                    <option value="BBM">BBM</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <input type="number" class="form-control" id="semester" name="semester" min="1" max="8" required>
            </div>
            <div class="form-group">
                <label for="roleid">Your Role ID</label>
                <input type="text" class="form-control" id="roleid" name="roleid" value="<?php echo htmlspecialchars($roleid); ?>" readonly required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="teacherindex.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"
        integrity="sha384-u8Hzfwze6ab3vIVj8Ft6qqXcZ9XzIWJw6u9+fq7l9I8w7S3pA1gZ0nPLZCf4nsM8"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8sh/jFcUs7XAWL7PR9OJ3R6P/JF9TA5z4G5DlH"
        crossorigin="anonymous"></script>
</body>

</html>
