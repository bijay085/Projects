<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

// Define columns to fetch and display
$columns = [
    'userid' => 'User ID',
    'roleid' => 'Role ID',
    'username' => 'Username',
    'photo' => 'Photo',
    'semester' => 'Semester',
    'gender' => 'Gender',
    'address' => 'Address',
    'admission_date' => 'Admission Date'
];

// Fetch necessary columns from tbl_users
$sql = "SELECT * FROM tbl_users";

$result = $conn->query($sql);

if ($result === false) {
    // Handle SQL error
    die('Error executing the query: ' . $conn->error);
}

// Initialize an array to hold user data
$users = [];

if ($result->num_rows > 0) {
    // Output data of each row into an array
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.min.css">
    <!-- Include jQuery -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0px;
        }

        .container {
            max-width: 1500px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .user-photo {
            max-width: 80px;
            max-height: 80px;
            border-radius: 50%;
        }

        .user-photo:hover {
            max-width: 150px;
            max-height: 150px;
        }

        .action-links a {
            padding: 8px 15px;
            margin-right: 5px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s, color 0.3s;
        }

        .action-links a.edit {
            background-color: #007bff;
        }

        .action-links a.edit:hover {
            background-color: #0056b3;
        }

        .action-links a.delete {
            background-color: #dc3545;
        }

        .action-links a.delete:hover {
            background-color: #c82333;
        }

        .action-links a.view {
            background-color: #28a745;
        }

        .action-links a.view:hover {
            background-color: #218838;
        }

        .dataTables_wrapper .dataTables_length {
            margin-bottom: 10px;
        }

        .dataTables_wrapper .dataTables_length select {
            width: 75px;
        }

        .dataTables_wrapper .dataTables_info {
            display: none;
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
    <div class="navbar">
        <a href="adminindex.php" class="active">Users</a>
        <a href="assignment.php">Assignment</a>
        <a href="roleassign.php">Roles</a>
        <a href="../badge/badgeindex.php">Badge</a>
        <a href="../overall/overall_index.php">Overall</a>
    </div>
    </nav>
    <h1>Admin Dashboard</h1>

    <div class="container">
        <table id="user-table" class="display">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $label): ?>
                        <th data-column="<?php echo $key; ?>"><?php echo $label; ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <?php foreach ($columns as $key => $label): ?>
                            <?php if ($key == 'photo'): ?>
                                <td data-column="<?php echo $key; ?>">
                                    <img src="../../uploads/<?php echo $user[$key]; ?>" alt="User Photo" class="user-photo">
                                </td>
                            <?php else: ?>
                                <td data-column="<?php echo $key; ?>"><?php echo $user[$key]; ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td class="action-links">
                            <a href="edit_user.php?userid=<?php echo $user['userid']; ?>" class="edit">Edit</a>
                            <a href="delete_user.php?userid=<?php echo $user['userid']; ?>" class="delete"
                                onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                            <a href="profile.php?userid=<?php echo $user['roleid']; ?>" class="view">View Profile</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            var table = $('#user-table').DataTable({
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 10,
                "columnDefs": [{ "targets": 'no-sort', "orderable": false }]
            });

            $('#user-table-length').change(function () {
                table.page.len($(this).val()).draw();
            });

            $('a.delete-user').click(function (e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (confirm('Are you sure you want to delete this user?')) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (response) {
                            alert(response);
                            table.ajax.reload();
                        },
                        error: function () {
                            alert('Error deleting user.');
                        }
                    });
                }
            });

        });
    </script>
</body>

</html>