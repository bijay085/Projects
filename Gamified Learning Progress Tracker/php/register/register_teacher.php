<?php
session_start();

// Include your database connection file
include_once 'D:\xampp\htdocs\glptwor\php\co.php';

// Function to sanitize input data
function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Start output buffering
ob_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize each input
    $username = sanitizeInput($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $fullname = sanitizeInput($_POST['fullname']);
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);

    // Handle photo upload
    $target_dir = "D:/xampp/htdocs/glptwor/uploads/";
    $target_file = $target_dir.basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["photo"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if (
        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif"
    ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            echo "The file " . htmlspecialchars(basename($_FILES["photo"]["name"])) . " has been uploaded.";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }

    // Insert data into database if photo uploaded successfully
    if ($uploadOk == 1) {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO tbl_users (roleid, username, password, fullname, dob, gender, address, phone, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            exit();
        }

        $roleid = $_SESSION['roleid']; // Get roleid from session

        // Bind parameters
        $stmt->bind_param("sssssssss", $roleid, $username, $password, $fullname, $dob, $gender, $address, $phone, $target_file);
        // Execute statement
        if ($stmt->execute()) {
            // echo "New record inserted successfully.";
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }
}

// Close connection
$conn->close();

// End output buffering and flush it
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }

        .container {
            width: 400px;
            padding: 20px;
            background-color: #F0F0F0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.6);
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }

        .form-group input,
        .form-group select {
            width: calc(100% - 16px);
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group select {
            font-size: 14px;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .buttons button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .buttons button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 12px;
            display: block;
            margin-top: 4px;
        }

        #teacherhead {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <form id="teacherForm" action="#" method="post" enctype="multipart/form-data" novalidate>
            <h2 id="teacherhead">Teacher Registration Form</h2>
            <input type="hidden" name="roleid" id="roleid">
            <!-- Section 1 -->
            <div class="form-section" id="section1">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                    <span id="usernameError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <span id="passwordError" class="error"></span>
                </div>
            </div>

            <!-- Section 2 -->
            <div class="form-section hidden" id="section2">
                <div class="form-group">
                    <label for="fullname">Full Name:</label>
                    <input type="text" id="fullname" name="fullname" required>
                    <span id="fullnameError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required>
                    <span id="dobError" class="error"></span>
                </div>
            </div>

            <!-- Section 3 -->
            <div class="form-section hidden" id="section3">
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span id="genderError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                    <span id="addressError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required>
                    <span id="phoneError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="photo">Photo:</label>
                    <input type="file" id="photo" name="photo" accept="image/*" required>
                    <span id="photoError" class="error"></span>
                </div>
            </div>

            <!-- Navigation buttons -->
            <div class="buttons">
                <button type="button" id="prevBtn" onclick="prevSection()">Previous</button>
                <button type="button" id="nextBtn" onclick="nextSection()">Next</button>
                <button type="submit" id="submitBtn">Submit</button>
            </div>
        </form>
    </div>

    <script>
        // Form elements and error messages
        const form = document.getElementById('teacherForm');
        const sections = document.querySelectorAll('.form-section');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        let currentSection = 0;

        // Function to validate each field
        function validateField(fieldId, regex, errorMsgId, errorMsg) {
            const field = document.getElementById(fieldId);
            const errorSpan = document.getElementById(errorMsgId);
            const value = field.value.trim();

            if (value === '') {
                errorSpan.textContent = 'This field should not be empty.';
                return false;
            } else if (!regex.test(value)) {
                errorSpan.textContent = errorMsg;
                return false;
            } else {
                errorSpan.textContent = '';
                return true;
            }
        }

        // Regular expressions and error messages for validation
        const fieldValidations = {
            username: {
                regex: /^[a-zA-Z0-9]([a-zA-Z0-9_]{0,24})$/,
                errorMsg: 'Username must be alphanumeric (letters, numbers, underscores only).'
            },
            password: {
                regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/,
                errorMsg: 'Password must contain at least 8 characters, including one uppercase letter, one lowercase letter, one digit, and one special symbol.'
            },
            fullname: {
                regex: /^[A-Za-z\s]{1,20}$/,
                errorMsg: 'Full name can only contain alphabets and spaces, up to 20 characters.'
            },
            dob: {
                regex: /^\d{4}-\d{2}-\d{2}$/,
                errorMsg: 'Please enter a valid date in YYYY-MM-DD format.'
            },
            gender: {
                regex: /^(male|female|other)$/,
                errorMsg: 'Please select a valid gender.'
            },
            address: {
                regex: /^[a-zA-Z0-9\s,-]+$/,
                errorMsg: 'Address can only contain letters, numbers, spaces, comma, and hyphen.'
            },
            phone: {
                regex: /^\d{10}$/,
                errorMsg: 'Phone number must be exactly 10 digits.'
            }
        };

        // Function to show/hide sections
        function showSection(sectionIndex) {
            sections.forEach((section, index) => {
                if (index === sectionIndex) {
                    section.classList.remove('hidden');
                } else {
                    section.classList.add('hidden');
                }
            });
            currentSection = sectionIndex;
        }

        // Validate all fields on form submit
        form.addEventListener('submit', function (event) {
            let isValid = true;

            for (let fieldId in fieldValidations) {
                const validation = fieldValidations[fieldId];
                if (!validateField(fieldId, validation.regex, `${fieldId}Error`, validation.errorMsg)) {
                    isValid = false;
                }
            }

            if (!isValid) {
                event.preventDefault(); // Prevent form submission if any field is invalid
            }
        });

        // Validate fields on keyup or change
        for (let fieldId in fieldValidations) {
            const validation = fieldValidations[fieldId];
            const field = document.getElementById(fieldId);

            field.addEventListener('keyup', () => {
                validateField(fieldId, validation.regex, `${fieldId}Error`, validation.errorMsg);
            });
        }

        // Function to navigate to the next section
        function nextSection() {
            if (currentSection < sections.length - 1) {
                currentSection++;
                showSection(currentSection);
            }
        }

        // Function to navigate to the previous section
        function prevSection() {
            if (currentSection > 0) {
                currentSection--;
                showSection(currentSection);
            }
        }

        // Initially show the first section
        showSection(currentSection);
    </script>
</body>

</html>
