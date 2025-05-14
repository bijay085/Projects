<?php
$uploadsPath = '../../../uploads/';
$badgePath = '../../badge/badgeicon/';

include 'D:/xampp/htdocs/glptwor/php/co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    header('Location: ../register/login.php');
    exit;
}

// Fetch all badges from the database
$query = "SELECT badgeid, name, photo FROM tbl_badges";
$result = $conn->query($query);

if ($result === false) {
    die("Error fetching badges: " . $conn->error);
}

$badges = $result->fetch_all(MYSQLI_ASSOC);

// Fetch users for the initial display
$usersQuery = "SELECT userid, fullname, username, roleid FROM tbl_users";
$usersResult = $conn->query($usersQuery);

if ($usersResult === false) {
    die("Error fetching users: " . $conn->error);
}

$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignBadges'])) {
    $userid = $_POST['userid'];
    $selectedBadges = $_POST['badges'] ?? [];

    // First, remove existing badges for the user
    $deleteQuery = "DELETE FROM tbl_user_badges WHERE userid = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("s", $userid);
    $stmt->execute();

    // Assign new badges to the user
    $insertQuery = "INSERT INTO tbl_user_badges (userid, badgeid) VALUES (?, ?)";
    $stmt = $conn->prepare($insertQuery);

    foreach ($selectedBadges as $badgeid) {
        $stmt->bind_param("ss", $userid, $badgeid);
        $stmt->execute();
    }

    // echo "Badges successfully assigned!";
    header('location:badgeStd.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Assign Badges</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 10px;
        }

        select, .badges-container {
            margin-bottom: 20px;
        }

        .badges-container {
            display: flex;
            flex-wrap: wrap;
        }

        .badge {
            margin: 10px;
            padding: 10px;
            background-color: #e9e9e9;
            border-radius: 8px;
            display: flex;
            align-items: center;
        }

        .badge img {
            margin-right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
        }

        button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .search-bar input {
            width: 100%;
            padding: 8px;
            font-size: 16px;
        }

        .user-list {
            display: none;
            margin-top: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
        }

        .user-item {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-item button {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 8px;
        }

        .user-item button:hover {
            background-color: #45a049;
        }

        .clear-selection {
            margin-top: 10px;
            background-color: #f44336;
            border: none;
            color: white;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 8px;
        }

        .clear-selection:hover {
            background-color: #e31b0c;
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
    </style>
    <script>
        let users = <?php echo json_encode($users); ?>;

        function filterUsers() {
            const searchTerm = document.getElementById('searchTerm').value.toLowerCase();
            const userSelection = document.getElementById('userSelection');
            userSelection.innerHTML = '';

            if (searchTerm === '') {
                userSelection.style.display = 'none';
                return;
            }

            users.forEach(user => {
                const userName = user.username.toLowerCase();
                const fullName = user.fullname.toLowerCase();
                const roleid = user.roleid.toLowerCase();
                const matchesSearch = userName.includes(searchTerm) || fullName.includes(searchTerm) || roleid.includes(searchTerm);

                if (matchesSearch) {
                    const userItem = document.createElement('div');
                    userItem.className = 'user-item';
                    userItem.setAttribute('data-username', user.username);
                    userItem.setAttribute('data-fullname', user.fullname);
                    userItem.setAttribute('data-roleid', user.roleid);
                    userItem.innerHTML = `
                        <span>${user.fullname} (${user.username}, ${user.roleid})</span>
                        <button type="button" onclick="selectUser('${user.userid}', '${user.fullname}')">Select</button>
                    `;
                    userSelection.appendChild(userItem);
                }
            });

            userSelection.style.display = 'block';
        }

        function selectUser(userid, fullname) {
            document.getElementById('selectedUser').value = userid;
            document.getElementById('displaySelectedUser').textContent = fullname;
            document.getElementById('userSelection').style.display = 'none';
            document.getElementById('clearSelection').style.display = 'inline';
        }

        function clearSelection() {
            document.getElementById('selectedUser').value = '';
            document.getElementById('displaySelectedUser').textContent = '';
            document.getElementById('clearSelection').style.display = 'none';
            document.getElementById('searchTerm').value = '';
            document.getElementById('userSelection').style.display = 'none';
        }
    </script>
</head>

<body>
<div class="navbar">
        <a href="badgeindex.php">Badge Index</a>
        <a href="badgeadd.php">Add New Badge</a>
        <a href="badgeassign.php" class="active">Assign Badge</a>
        <a href="badgeStd.php">Student Badge</a>
    </div>
    <div class="container">
        <h1>Assign Badges</h1>
        <div class="search-bar">
            <input type="text" id="searchTerm" placeholder="Search by username, fullname, or roleid" onkeyup="filterUsers()">
        </div>
        <div id="userSelection" class="user-list"></div>
        <form method="post" action="">
            <input type="hidden" id="selectedUser" name="userid" required>
            <div>
                <label for="selectedUser">Selected User: <span id="displaySelectedUser"></span></label>
                <button type="button" id="clearSelection" class="clear-selection" style="display: none;" onclick="clearSelection()">Clear</button>
            </div>

            <div class="badges-container">
                <?php foreach ($badges as $badge): ?>
                    <div class="badge">
                        <input type="checkbox" name="badges[]" value="<?php echo htmlspecialchars($badge['badgeid']); ?>">
                        <img src="<?php echo $badgePath . htmlspecialchars($badge['photo']); ?>" alt="<?php echo htmlspecialchars($badge['name']); ?>">
                        <?php echo htmlspecialchars($badge['name']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="assignBadges">Assign Badges</button>
        </form>
    </div>
</body>

</html>
