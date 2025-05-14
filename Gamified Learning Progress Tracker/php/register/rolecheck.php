<?php
session_start();
include 'D:\xampp\htdocs\glptwor\php\co.php';

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roleid = $_POST['roleid'];
    $roletype = $_POST['roletype'];

    // Check if roleid and roletype both exist in tbl_roles
    $stmt = $conn->prepare("SELECT roleid FROM tbl_roles WHERE roleid = ? AND roletype = ?");
    $stmt->bind_param("ss", $roleid, $roletype);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Initialize $stmt_user before use
        $stmt_user = $conn->prepare("SELECT roleid FROM tbl_users WHERE roleid = ?");
        $stmt_user->bind_param("s", $roleid);
        $stmt_user->execute();
        $stmt_user->store_result();

        if ($stmt_user->num_rows > 0) {

            $error_msg = "This role ID is already registered. Please proceed to login.";
        } else {
            // roleid and roletype exist, save roleid and roletype in session
            $_SESSION['roleid'] = $roleid;
            $_SESSION['roletype'] = $roletype;

            // Redirect based on roletype
            if ($roletype === 'student') {
                header("Location: register_student.php");
                exit();
            } elseif ($roletype === 'teacher') {
                header("Location: register_teacher.php");
                exit();
            }
        }

        // Close $stmt_user after use
        $stmt_user->close();
    } else {
        // roleid and roletype do not match
        $error_msg = "*Role ID and Role Type do not match in the database.<br> *Means you are not registered user. <br> *Contact college admin if there is any problem.";
    }

    // Close $stmt after use
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Role Entry Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background: linear-gradient(to right, #c7bacf, #e0c5f0); */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.9)), url('rolecheck.jpg');
            background-size: cover;
            background-repeat: no-repeat;
        }

        .container {
            background: #fff;
            padding: 10px 15px;
            border: 1px ;
            border-radius: 12px ;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.8);
            text-align: center;
            width: 350px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        input[type="submit"],
        input[type="reset"] {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background-color: #3498db;
            color: #fff;
            font-size: 16px;
            margin: 25px;
            margin-left: 40px;
            transition: background-color 0.3s;
        }

        input[type="reset"] {
            background-color: #d45965;
        }
        
        input[type="submit"]:hover {
            background-color: #51dcf5;
            color : black;
            font-size: 16px;
        }

        input[type="reset"]:hover {
            background-color: #b50b16;
            color : black;
        }

        .success {
            color: green;
            font-weight: bold;
        }

        .error {
            color: rgb(232, 35, 35);
            font-style: italic;
            text-align: left;
        }

        .message {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Enter Role</h2>
        <form id="roleForm" method="post" action="rolecheck.php">
            <label for="roleid">Role ID:</label>
            <input type="text" id="roleid" name="roleid" required>
            <label for="roletype">Role Type:</label>
            <select id="roletype" name="roletype" required>
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
            <input type="submit" value="Submit">
            <input type="reset" value="Cancel">

            <p>Already have account : <a href="login.php">Login</a></p>
        </form>
        <div class="message">
            <?php
            if (!empty($error_msg)) {
                echo "<p class='error'>$error_msg</p>";
            }
            ?>
        </div>
    </div>
    <script>document.addEventListener('DOMContentLoaded', function () {
            const roleForm = document.getElementById('roleForm');
            const roleidInput = document.getElementById('roleid');
            const roletypeSelect = document.getElementById('roletype');
            const messageDiv = document.querySelector('.message');

            const roleidPattern = /^[ST][a-zA-Z0-9]{0,14}$/;

            function validateRoleID() {
                const roleidValue = roleidInput.value;
                if (!roleidPattern.test(roleidValue)) {
                    return "Role ID must start with S or T, contain only alphanumeric characters, and be no longer than 15 characters.";
                }
                return "";
            }

            function validateForm(event) {
                messageDiv.innerHTML = "";

                let errorMessage = validateRoleID();
                if (errorMessage) {
                    event.preventDefault();
                    messageDiv.innerHTML = `<p class='error'>${errorMessage}</p>`;
                    return;
                }

                if (!roletypeSelect.value) {
                    event.preventDefault();
                    messageDiv.innerHTML = "<p class='error'>Please select a role type.</p>";
                    return;
                }
            }

            roleidInput.addEventListener('keypress', function (event) {
                const key = event.key;
                if (!/^[a-zA-Z0-9]$/.test(key)) {
                    event.preventDefault();
                }
            });

            roleidInput.addEventListener('keyup', function () {
                const errorMessage = validateRoleID();
                if (errorMessage) {
                    messageDiv.innerHTML = `<p class='error'>${errorMessage}</p>`;
                } else {
                    messageDiv.innerHTML = "";
                }
            });

            roleForm.addEventListener('submit', validateForm);
        });
    </script>
</body>

</html>