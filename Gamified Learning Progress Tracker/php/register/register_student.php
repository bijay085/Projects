<?php
session_start();

// Include your database connection file
include_once 'D:\xampp\htdocs\glptwor\php\co.php';

// Function to sanitize input data
function sanitizeInput($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize each input
    $username = sanitizeInput($_POST['username']);
    // $password = sanitizeInput($_POST['password']);
    $password = password_hash(sanitizeInput($_POST['password']), PASSWORD_DEFAULT); // Hash the password
    $fullname = sanitizeInput($_POST['fullname']);
    $dob = sanitizeInput($_POST['dob']);
    $gender = sanitizeInput($_POST['gender']);
    $address = sanitizeInput($_POST['address']);
    $phone = sanitizeInput($_POST['phone']);
    $program = sanitizeInput($_POST['program']);
    $semester = sanitizeInput($_POST['semester']);
    $admission_date = sanitizeInput($_POST['admission_date']);

    // Handle photo upload
    $target_dir = "D:/xampp/htdocs/glptwor/uploads/";
    $target_file = $target_dir.basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["photo"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    $allowedFormats = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedFormats)) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Try to upload file if no errors
    if ($uploadOk && move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        echo "The file " . htmlspecialchars(basename($_FILES["photo"]["name"])) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
        $uploadOk = 0;
    }

    // Insert data into database if photo uploaded successfully
    if ($uploadOk) {
        $stmt = $conn->prepare("INSERT INTO tbl_users (roleid, username, password, fullname, dob, gender, address, phone, program, semester, admission_date, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $roleid = $_SESSION['roleid']; // Get roleid from session
            $stmt->bind_param("ssssssssssss", $roleid, $username, $password, $fullname, $dob, $gender, $address, $phone, $program, $semester, $admission_date, $target_file);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.9)), url('loginstudent.jpg');
            background-size: cover;
            background-repeat: no-repeat;
        }

        .container {
            width: 400px;
            padding: 20px;
            background-color: #F0F0F0;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        }

        .section {
            display: none;
        }

        #section1 {
            display: block;
        }

        #studenthead {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <form id="studentForm" action="#" method="post" enctype="multipart/form-data">
            <h2 id="studenthead">Student Registration Form</h2>
            <input type="hidden" name="roleid" id="roleid">

            <div id="section1" class="section">
                <div class="form-group">
                    <label for="username">Username*:</label>
                    <input type="text" id="username" name="username" placeholder="example123">
                    <span id="usernameError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password*:</label>
                    <input type="password" id="password" name="password">
                    <span id="passwordError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="fullname">Full Name*:</label>
                    <input type="text" id="fullname" name="fullname" placeholder="First Middle Last">
                    <span id="fullnameError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob">
                    <span id="dobError" class="error"></span>
                </div>
            </div>

            <div id="section2" class="section">
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <span id="genderError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="address">Address*:</label>
                    <input type="text" id="address" name="address">
                    <span id="addressError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="phone">Phone*:</label>
                    <input type="text" id="phone" name="phone">
                    <span id="phoneError" class="error"></span>
                </div>
            </div>

            <div id="section3" class="section">
                <div class="form-group">
                    <label for="program">Program*:</label>
                    <input type="text" id="program" name="program" placeholder="BCA">
                    <span id="programError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="semester">Semester*:</label>
                    <input type="number" id="semester" name="semester" placeholder="1-8">
                    <span id="semesterError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="admission_date">Year/Month of Admission:</label>
                    <input type="date" id="admission_date" name="admission_date">
                    <span id="admissionDateError" class="error"></span>
                </div>
                <div class="form-group">
                    <label for="photo">Photo*:</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <span id="photoError" class="error"></span>
                </div>
            </div>

            <div class="buttons">
                <button type="button" onclick="previousSection()" id="prevBtn">Previous</button>
                <button type="button" onclick="nextSection()" id="nextBtn">Next</button>
                <button type="submit">Submit</button>
            </div>
        </form>
    </div>

    <script>
        let currentSection = 1;
        const totalSections = 3;

        // Function to clear fields and errors in a section
        function clearSection(sectionNum) {
            const section = document.getElementById(`section${sectionNum}`);
            const inputs = section.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.type === 'file') {
                    input.value = ''; // Clear file input
                } else {
                    input.value = ''; // Clear text input, select, etc.
                }
            });

            // Clear error messages
            const errorSpans = section.querySelectorAll('.error');
            errorSpans.forEach(span => {
                span.textContent = '';
            });
        }

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
                regex: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/,
                errorMsg: 'Password must contain at least 8 characters, including one uppercase letter, one lowercase letter, one digit, and one special symbol.'
            },
            fullname: {
                regex: /^([A-Z][a-z]{1,19})(?:\s([A-Z][a-z]{1,19})){0,2}\s?$/,
                errorMsg: 'Full name can only contain alphabets, up to 20 characters, and up to two spaces.'
            },
            address: {
                regex: /^[a-zA-Z0-9\s,-]+$/,
                errorMsg: 'Address can only contain letters, numbers, spaces, comma, and hyphen.'
            },
            phone: {
                regex: /^9\d{9}$/,
                errorMsg: 'Phone number must be exactly 10 digits starting with 9.'
            },
            program: {
                regex: /^(BCA|other)$/,
                errorMsg: 'Program must be either "BCA" or "other".'
            },
            semester: {
                regex: /^[1-8]$/,
                errorMsg: 'Semester must be a number between 1 and 8.'
            }
        };

        // Validate all fields on form submit
        document.getElementById('studentForm').addEventListener('submit', function (event) {
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

        // Function to show section and prevent default if validation fails
        function showSection(sectionNum) {
            let isValid = true;

            // Validate current section fields
            if (currentSection === 1) {
                ['username', 'password', 'fullname'].forEach(fieldId => {
                    const validation = fieldValidations[fieldId];
                    if (!validateField(fieldId, validation.regex, `${fieldId}Error`, validation.errorMsg)) {
                        isValid = false;
                    }
                });
            } else if (currentSection === 2) {
                ['address', 'phone'].forEach(fieldId => {
                    const validation = fieldValidations[fieldId];
                    if (!validateField(fieldId, validation.regex, `${fieldId}Error`, validation.errorMsg)) {
                        isValid = false;
                    }
                });
            } else if (currentSection === 3) {
                ['program', 'semester'].forEach(fieldId => {
                    const validation = fieldValidations[fieldId];
                    if (!validateField(fieldId, validation.regex, `${fieldId}Error`, validation.errorMsg)) {
                        isValid = false;
                    }
                });
            }

            // If valid, proceed to next section
            if (isValid) {
                document.getElementById(`section${sectionNum}`).style.display = 'block';
                currentSection = sectionNum;
            }
        }

        // Previous section function
        function previousSection() {
            if (currentSection > 1) {
                document.getElementById(`section${currentSection}`).style.display = 'none';
                clearSection(currentSection); // Clear fields and errors for the current section
                currentSection--;
            }
        }

        // Next section function
        function nextSection() {
            showSection(currentSection + 1);
        }

        // Initialize form with the first section visible
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('section1').style.display = 'block';
        });
    </script>

</body>

</html>
