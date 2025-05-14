<?php
$uploadsPath = '../../uploads/';
$badgePath = '../badge/badgeicon/';

include 'D:/xampp/htdocs/glptwor/php/co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    header('Location: ../register/login.php');
    exit;
}

$roleid = $_GET['userid'];

$query = "SELECT u.*, 
                 b.badgeid, 
                 b.photo AS badge_photo, 
                 b.name AS badge_name, 
                 b.description AS badge_description,
                 o.description, 
                 o.result, 
                 o.class_activity, 
                 o.performance_rate, 
                 o.behavior, 
                 o.overall, 
                 o.programming_language
          FROM tbl_users u
          LEFT JOIN tbl_user_badges ub ON u.userid = ub.userid
          LEFT JOIN tbl_badges b ON ub.badgeid = b.badgeid
          LEFT JOIN tbl_overall o ON u.roleid = o.roleid
          WHERE u.roleid = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param("s", $roleid);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

if (empty($users)) {
    echo "No user found with role ID: " . htmlspecialchars($roleid);
    die();
}

$userResult = $users[0]; // Assuming all user details are the same for the same roleid except badges
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.9)), url('profile.webp');
            background-size: cover;
            background-repeat: no-repeat;
            color: #fff;
            z-index: 1;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0 0 7px 7px;
            background-color: #333;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(57, 94, 105, 0.4);
            z-index: 2;
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

        .profile-container {
            max-width: 800px;
            background-color: rgba(155, 174, 212, 0.9);
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 40px auto;
            color: #333;
            z-index: 1;
        }

        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            margin-bottom: 20px;
            position: relative;
            border-radius: 50%;
        }

        .profile-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .badge-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: -20px;
        }

        .badge {
            background-color: #dfe9f0;
            border-radius: 16px;
            width: 80px;
            height: 24px;
            margin: 10px;
            overflow: hidden;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .badge img {
            width: 20px;
            height: 20px;
            object-fit: cover;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .badge:hover img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }

        .tooltip {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #333;
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s;
            z-index: 100;
        }

        .badge:hover .tooltip {
            opacity: 1;
            visibility: visible;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 40px;
            width: 100%;
        }

        .skill-bar {
            margin: 15px 0;
            width: 100%;
            height: 15px;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
        }

        .skill-bar-inner {
            height: 100%;
            background-color: #4caf50;
            border-radius: 5px;
            text-align: right;
            color: white;
            line-height: 15px;
            position: absolute;
            left: 0;
            top: 0;
            transition: width 0.5s ease-in-out, background-color 0.3s;
            cursor: no-drop;
        }

        .info-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: color 0.3s;
            margin: 40px;
        }

        .info-box h2 {
            margin: 10px 0;
            color: #333;
        }

        .info-box p {
            margin: 5px 0;
            color: #666;
        }

        .info-box:hover {
            color: rgb(107, 104, 104);
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="stdindex.php">Home</a>
        <a href="studentDisplayAssignment.php">Assignment</a>
        <a href="profile.php" class="active">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-img">
                <img src="<?php echo $uploadsPath . htmlspecialchars($userResult['photo']); ?>" alt="Profile Picture">
            </div>
            <h2><?php echo htmlspecialchars($userResult['fullname']); ?></h2>
        </div>
        <div class="badge-container">
            <?php foreach ($users as $user): ?>
                <?php if (!empty($user['badge_photo'])): ?>
                    <div class="badge" data-name="<?php echo htmlspecialchars($user['badge_name']); ?>">
                        <img src="<?php echo $badgePath . htmlspecialchars($user['badge_photo']); ?>"
                            alt="<?php echo htmlspecialchars($user['badge_name']); ?>">
                        <div class="tooltip"><?php echo htmlspecialchars($user['badge_description']); ?></div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (empty($users[0]['badge_photo'])): ?>
                <p>No badges assigned.</p>
            <?php endif; ?>
        </div>
        <div class="info-grid">
            <div class="info-box user-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($userResult['username']); ?></p>
                <p><strong>Program:</strong> <?php echo htmlspecialchars($userResult['program']); ?></p>
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($userResult['semester']); ?></p>
                <p><strong>Admission Date:</strong> <?php echo htmlspecialchars($userResult['admission_date']); ?></p>
            </div>
            <div class="info-box">
                <h2>Result</h2>
                <p><?php echo htmlspecialchars($userResult['result'] ?? 'No result available') . ' in last exam'; ?></p>
            </div>
            <div class="info-box">
                <h2>Description</h2>
                <p><?php echo htmlspecialchars($userResult['description'] ?? 'No description available'); ?></p>
            </div>
            <div class="info-box">
                <h2>Skill And Skill bar</h2>
                <p>Programming language(s) :
                    <?php echo htmlspecialchars($userResult['programming_language'] ?? 'No programming languages listed'); ?>
                </p>
                <div class="skill-bar">
                    <div class="skill-bar-inner"
                        style="width: <?php echo htmlspecialchars($userResult['class_activity'] ?? '0'); ?>%;">
                        Class Activity: <?php echo htmlspecialchars($userResult['class_activity'] ?? '0'); ?>%
                    </div>
                </div>
                <div class="skill-bar">
                    <div class="skill-bar-inner"
                        style="width: <?php echo htmlspecialchars($userResult['performance_rate'] ?? '0'); ?>%;">
                        Performance Rate: <?php echo htmlspecialchars($userResult['performance_rate'] ?? '0'); ?>%
                    </div>
                </div>
                <div class="skill-bar">
                    <div class="skill-bar-inner"
                        style="width: <?php echo htmlspecialchars($userResult['behavior'] ?? '0'); ?>%;">
                        Behavior: <?php echo htmlspecialchars($userResult['behavior'] ?? '0'); ?>%
                    </div>
                </div>
                <div class="skill-bar">
                    <div class="skill-bar-inner"
                        style="width: <?php echo htmlspecialchars($userResult['overall'] ?? '0'); ?>%;">
                        Overall: <?php echo htmlspecialchars($userResult['overall'] ?? '0'); ?>%
                    </div>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="editprofile.php">Edit Profile</a>
        </div>
        <script>
            const badges = document.querySelectorAll('.badge');
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            document.body.appendChild(tooltip);

            badges.forEach(badge => {
                badge.addEventListener('mouseover', () => {
                    const description = badge.querySelector('.tooltip').textContent;
                    tooltip.textContent = description;
                    tooltip.style.opacity = 1;
                    tooltip.style.visibility = 'visible';
                    tooltip.style.top = '50%';
                    tooltip.style.left = '50%';
                    tooltip.style.transform = 'translate(-50%, -50%)';
                });
                badge.addEventListener('mouseout', () => {
                    tooltip.style.opacity = 0;
                    tooltip.style.visibility = 'hidden';
                });
            });
        </script>

    </div>
    </div>
</body>

</html>