<?php
include 'D:/xampp/htdocs/glptwor/php/co.php'; // Adjust path as necessary

// Function to sanitize input data
function sanitize($data)
{
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}

// Initialize variables
$roleid = '';
$roletype = '';
$action = 'add'; // Default action is to add a new role

// Check if form is submitted for adding a new role
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        // Validate and sanitize inputs
        $roleid = sanitize($_POST['roleid']);
        $roletype = sanitize($_POST['roletype']);

        // Check if roleid is provided
        if (empty($roleid)) {
            die('Role ID is required.');
        }

        // Check if roletype is valid
        if (!in_array($roletype, ['student', 'teacher'])) {
            die('Invalid role type.');
        }

        // Insert new role into database
        $sql = "INSERT INTO tbl_roles (roleid, roletype) VALUES ('$roleid', '$roletype')";
        if ($conn->query($sql) === TRUE) {
            // Redirect to avoid re-submission on refresh
            header("Location: roleassign.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Fetch roles based on filter or all roles
$sql = "SELECT * FROM tbl_roles";
if (isset($_GET['filter'])) {
    $filter = sanitize($_GET['filter']);
    if ($filter !== 'student' && $filter !== 'teacher') {
        $filter = ''; // Reset filter if invalid value
    } else {
        $sql .= " WHERE roletype='$filter'";
    }
}
$result = $conn->query($sql);

// Search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $sql = "SELECT * FROM tbl_roles WHERE roleid LIKE '%$search%' OR roletype LIKE '%$search%'";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role index | Manage Roles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
            margin: 0;
        }

        h3 {
            margin-top: 20px;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
        }

        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            font-size: 17px;
        }

        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }

        h2 {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        form {
            margin-top: 20px;
        }

        form input[type="text"],
        form select {
            padding: 8px;
            width: 200px;
        }

        form input[type="submit"] {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        form input[type="submit"]:hover {
            background-color: #2980b9;
        }

        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            color: #3498db;
        }

        .navbar .active {
            color: green;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>

</head>

<body>
    <h3>Admin Locator</h3>
    <div class="navbar">
        <a href="adminindex.php">Users</a>
        <a href="assignment.php">Assignment</a>
        <a href="roleassign.php" class="active"> Roles</a>
    </div>
    <h2>Manage Roles</h2>

    <!-- Search form -->
    <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="text" name="search" placeholder="Search by Role ID or Role Type">
        <input type="submit" value="Search">
    </form>

    <!-- Filter links -->
    <div style="margin-top: 10px;">
        <a href="?filter=student">Filter Students</a> |
        <a href="?filter=teacher">Filter Teachers</a> |
        <a href="?">Show All</a>
    </div>

    <!-- Display roles -->
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Role ID</th>
                <th>Role Type</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['roleid']); ?></td>
                    <td><?php echo htmlspecialchars($row['roletype']); ?></td>
                    <td>
                        <a href="roleEdit.php?roleid=<?php echo urlencode($row['roleid']); ?>">Edit</a>
                        <a href="roleDelete.php?roleid=<?php echo urlencode($row['roleid']); ?>">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No roles found.</p>
    <?php endif; ?>

    <!-- Form for adding new role -->
    <h3>Add New Role</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="action" value="add">
        Role ID: <input type="text" name="roleid" value="<?php echo htmlspecialchars($roleid); ?>"><br><br>
        Role Type:
        <select name="roletype">
            <option value="student" <?php echo ($roletype == 'student') ? 'selected' : ''; ?>>Student</option>
            <option value="teacher" <?php echo ($roletype == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
        </select><br><br>
        <input type="submit" value="Add Role">
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleidInput = document.querySelector('input[name="roleid"]');

            roleidInput.addEventListener('keyup', function () {
                validateRoleID();
            });

            roleidInput.addEventListener('keydown', function () {
                validateRoleID();
            });

            function validateRoleID() {
                let roleid = roleidInput.value.trim();

                // Validate conditions
                if (roleid.length > 0) {
                    if (/^[ST][a-zA-Z]$/.test(roleid) && roleid.length <= 15) {
                        // Valid input
                        roleidInput.setCustomValidity('');
                    } else {
                        // Invalid input
                        roleidInput.setCustomValidity('Role ID must start with S or T, be maximum 15 characters long, and consist of only one alphabetic character.');
                    }
                } else {
                    // Empty input
                    roleidInput.setCustomValidity('');
                }
            }

            // Ensure validation runs on initial page load
            validateRoleID();
        });
    </script>

</body>

</html>

<?php
// Close database connection
$conn->close();
?>