<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Fetch all records from tbl_overall along with full names from tbl_users
$query = "SELECT o.*, u.fullname FROM tbl_overall o LEFT JOIN tbl_users u ON o.roleid = u.roleid";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching records: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Overall Records</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 100%;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        th {
            background-color: #f7f7f7;
            color: #555;
            font-weight: bold;
        }

        td {
            background-color: #fff;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        tr:hover td {
            background-color: #f1f1f1;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .button {
            padding: 8px 12px;
            text-decoration: none;
            color: #fff;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
            transition: background-color 0.3s, transform 0.2s;
        }

        .button:hover {
            transform: scale(1.05);
        }

        .edit {
            background-color: #007bff;
        }

        .edit:hover {
            background-color: #0056b3;
        }

        .delete {
            background-color: #dc3545;
        }

        .delete:hover {
            background-color: #c82333;
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
            <a class="nav-link active" href="overallindex.php">Overall index</a>
            <a class="nav-link" href="../teacherindex.php">Assignment</a>
            <a class="nav-link" href="./allstudents.php">Student Assign overall</a>
            <a class="nav-link" href="../logout.php">Logout</a>
        </div>
    </nav>
    <div class="container">
        <h2>Overall Records</h2>
        <table>
            <thead>
                <tr>
                    <th>OID</th>
                    <th>Role ID</th>
                    <th>Full Name</th>
                    <th>Attendance</th>
                    <th>Assignment</th>
                    <th>Performance</th>
                    <th>Result</th>
                    <th>Description</th>
                    <th>Class Activity</th>
                    <th>Performance Rate</th>
                    <th>Behavior</th>
                    <th>Overall</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['oid']); ?></td>
                            <td><?php echo htmlspecialchars($row['roleid']); ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['attendance']); ?></td>
                            <td><?php echo htmlspecialchars($row['assignment']); ?></td>
                            <td><?php echo htmlspecialchars($row['performance']); ?></td>
                            <td><?php echo htmlspecialchars($row['result']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['class_activity']); ?></td>
                            <td><?php echo htmlspecialchars($row['performance_rate']); ?></td>
                            <td><?php echo htmlspecialchars($row['behavior']); ?></td>
                            <td><?php echo htmlspecialchars($row['overall']); ?></td>
                            <td class="actions">
                                <a href="editoverall.php?oid=<?php echo $row['oid']; ?>" class="button edit">Edit</a>
                                <a href="deleteoverall.php?oid=<?php echo $row['oid']; ?>" class="button delete"
                                    onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>
