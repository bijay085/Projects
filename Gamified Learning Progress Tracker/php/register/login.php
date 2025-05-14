<?php
session_start(); // Start the session
include_once 'D:\xampp\htdocs\glptwor\php\co.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs (if necessary)
    $roleid = $_POST['roleid'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check admin credentials first
    if ($roleid === 'Z123456' && $username === 'admin' && $password === 'Admin@123') {
        $_SESSION['username'] = $username;
        $_SESSION['roleid'] = $roleid;
        header('Location: ../admin/adminindex.php');
        exit;
    }

    // Prepare SQL statement to fetch hashed password and roleid based on username
    $sql = "SELECT password, roleid FROM tbl_users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . $conn->error); // Handle prepare error
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param('s', $username);
    $stmt->execute();

    if ($stmt->error) {
        die('Execute failed: ' . $stmt->error); // Handle execute error
    }

    // Bind the result variables
    $stmt->bind_result($hashed_password, $db_roleid);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($password, $hashed_password)) {
        // Password matches, proceed with role-based redirection
        $_SESSION['username'] = $username;
        $_SESSION['roleid'] = $db_roleid;

        // Determine redirection based on roleid prefix
        $first_char = substr($db_roleid, 0, 1);
        if ($first_char === 'S') {
            header('Location: ../student/stdindex.php');
            exit;
        } elseif ($first_char === 'T') {
            header('Location: ../teacher/teacherindex.php');
            exit;
        } else {
            // Handle unexpected roleid format or credentials
            echo 'Invalid roleid format. Please try again.';
            header('Location: login.php');
            exit;
        }
    } else {
        // Handle invalid username/password
        echo 'Invalid username or password. Please try again.';
        header('Location: login.php');
        exit;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* background-color: #f0f0f0; */
            /* background: linear-gradient(to right, #c7bacf, #e0c5f0); */
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.9)), url('login.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #F0F0F0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            max-width: 90%;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .login-container input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .login-container .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .login-container button {
            background-color: #3498db;
            /* Blue color */
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        #cancel-btn {
            background-color: #d45965;

        }

        #cancel-btn:hover {
            background-color: #b50b16;
            color: black;
            font-size: 15px;


        }

        .login-container button:hover {
            background-color: #2980b9;
            color: black;
            font-size: 15px;

        }

        .error-message {
            color: red;
            font-size: 12px;
            text-align: left;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>

        <form id="login-form" method="POST" action="#">
            <input type="text" id="id" name="roleid" placeholder="ID given by college" required>
            <div id="id-error" class="error-message"></div>
            <br>
            <input type="text" id="username" name="username" placeholder="Username" required>
            <div id="username-error" class="error-message"></div>
            <br>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div id="password-error" class="error-message"></div>
            <br>
            <div class="button-container">
                <button type="submit" id="login-btn">Login</button>
                <button type="button" id="cancel-btn">Cancel</button>
            </div>
            <p>Create new account : <a href="rolecheck.php">Sign up</a> </p>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var idInput = document.getElementById('id');
            var usernameInput = document.getElementById('username');
            var passwordInput = document.getElementById('password');
            var loginForm = document.getElementById('login-form');
            var cancelButton = document.getElementById('cancel-btn');

            idInput.addEventListener('keyup', validateId);
            usernameInput.addEventListener('keyup', validateUsername);
            passwordInput.addEventListener('keyup', validatePassword);
            loginForm.addEventListener('submit', function (event) {
                event.preventDefault(); // Prevent form submission
                validateForm();
            });
            cancelButton.addEventListener('click', function () {
                console.log('Cancel button clicked.');
                // Optional: Clear the form or redirect
            });

            function validateId() {
                var idValue = idInput.value.trim();
                var idError = document.getElementById('id-error');
                idError.textContent = '';

                if (!/^[a-zA-Z0-9]{5,}$/.test(idValue)) {
                    idError.textContent = 'ID should be alphanumeric and at least 5 characters long.';
                }
                idError.style.display = idValue.length > 0 && !/^[a-zA-Z0-9]{5,}$/.test(idValue) ? 'block' : 'none';
            }

            function validateUsername() {
                var usernameValue = usernameInput.value.trim();
                var usernameError = document.getElementById('username-error');
                usernameError.textContent = '';

                if (!/^[a-zA-Z0-9]{5,}$/.test(usernameValue)) {
                    usernameError.textContent = 'Username should be alphanumeric and at least 5 characters long.';
                }
                usernameError.style.display = usernameValue.length > 0 && !/^[a-zA-Z0-9]{5,}$/.test(usernameValue) ? 'block' : 'none';
            }

            function validatePassword() {
                var passwordValue = passwordInput.value.trim();
                var passwordError = document.getElementById('password-error');
                passwordError.textContent = '';

                if (passwordValue.length < 8) {
                    passwordError.textContent = 'Password should be at least 8 characters long.';
                } else if (!/[a-z]/.test(passwordValue)) {
                    passwordError.textContent = 'Password should contain at least one lowercase letter.';
                } else if (!/[A-Z]/.test(passwordValue)) {
                    passwordError.textContent = 'Password should contain at least one uppercase letter.';
                } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)) {
                    passwordError.textContent = 'Password should contain at least one special character.';
                }
                passwordError.style.display = passwordValue.length > 0 && (
                    passwordValue.length < 8 || !/[a-z]/.test(passwordValue) || !/[A-Z]/.test(passwordValue) || !/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)
                ) ? 'block' : 'none';
            }

            function validateForm() {
                validateId();
                validateUsername();
                validatePassword();

                var errorMessages = document.querySelectorAll('.error-message');
                var isError = false;
                errorMessages.forEach(function (error) {
                    if (error.style.display === 'block') {
                        isError = true;
                    }
                });

                if (!isError) {
                    // Proceed with form submission
                    loginForm.submit();
                }
            }
        });
    </script>

</body>

</html>