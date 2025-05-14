<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';
// Fetch all students from tbl_users whose roleid starts with 'S'
$query = "SELECT u.userid, u.roleid, u.fullname, o.class_activity, o.performance_rate, o.behavior, o.overall 
          FROM tbl_users u
          LEFT JOIN tbl_overall o ON u.roleid = o.roleid
          WHERE u.roleid LIKE 'S%'";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching records: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 16px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .button {
            padding: 8px 15px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 14px;
            text-align: center;
        }

        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #343a40;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 0 0 8px 8px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 4px;
            position: relative;
        }

        .navbar a:hover,
        .navbar a.active {
            background-color: #495057;
            color: #f8f9fa;
        }

        .navbar a.active {
            background: #007bff;
        }

        .navbar a.active:after {
            content: '';
            display: block;
            width: 100%;
            height: 3px;
            background: #f8f9fa;
            position: absolute;
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
    </style>
</head>
<body>
<nav class="navbar">
        <div class="navbar-links">
            <a class="nav-link" href="overall_index.php">Overall index</a>
            <a class="nav-link" href="../admin/assignment.php">Assignment</a>
            <a class="nav-link active" href="./allstudents.php">Student Assign overall</a>
            <a class="nav-link" href="../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h2>All Students</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Role ID</th>
                    <th>Full Name</th>
                    <th>Class Activity</th>
                    <th>Performance Rate</th>
                    <th>Behavior</th>
                    <th>Overall</th>

                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['userid']); ?></td>
                        <td><?php echo htmlspecialchars($row['roleid']); ?></td>
                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($row['class_activity']); ?></td>
                        <td><?php echo htmlspecialchars($row['performance_rate']); ?></td>
                        <td><?php echo htmlspecialchars($row['behavior']); ?></td>
                        <td><?php echo htmlspecialchars($row['overall']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
