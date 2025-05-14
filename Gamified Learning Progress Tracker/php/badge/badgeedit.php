<?php
include('D:\xampp\htdocs\glptwor\php\co.php');

$name = $description = '';
$name_err = $description_err = '';
$photo_err = '';

// Check if ID parameter is provided in URL
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $sql = "SELECT * FROM tbl_badges WHERE badgeid = ?";

    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("i", $param_id);
        $param_id = trim($_GET["id"]);

        if($stmt->execute()){
            $result = $stmt->get_result();

            if($result->num_rows == 1){
                $row = $result->fetch_array(MYSQLI_ASSOC);

                $name = $row["name"];
                $description = $row["description"];
                $photo = $row["photo"];
            } else{
                echo("Something wrong ! Please try again with new valid data");
                exit();
            }
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter a name for the badge.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    } else {
        $description = trim($_POST["description"]);
    }

    // Check if file was uploaded without errors
    if ($_FILES["photo"]["error"] == 0) {
        $target_dir = "D:/xampp/htdocs/glptwor/php/badge/badgeicon/";
        $temp_file = $_FILES["photo"]["tmp_name"]; // Temporary file path
        $target_file = basename($_FILES["photo"]["name"]); // Permanent file path
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($temp_file);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $photo_err = "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $photo_err = "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["photo"]["size"] > 500000) {
            $photo_err = "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif") {
            $photo_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            $photo_err .= " Your file was not uploaded.";
        } else {
            // if everything is ok, try to upload file
            if (move_uploaded_file($temp_file, $target_file)) {
                // File uploaded successfully, now save the relative path to database
                $photo = basename($_FILES["photo"]["name"]); // Save relative path to database
            } else {
                $photo_err = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Check input errors before updating into database
    if (empty($name_err) && empty($description_err) && empty($photo_err)) {
        // Prepare an update statement
        $sql = "UPDATE tbl_badges SET name=?, photo=?, description=? WHERE badgeid=?";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssi", $param_name, $param_photo, $param_description, $param_id);

            // Set parameters
            $param_name = $name;
            $param_photo = empty($photo) ? $photo : $photo; // Update only if a new photo is uploaded
            $param_description = $description;
            $param_id = trim($_GET["id"]);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to badge index page
                header("location: badgeindex.php");
                exit();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Badge</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body{
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .form-group .help-block {
            color: red;
            font-size: 14px;
        }

        .form-group input[type="submit"] {
            background-color: #4CAF50;
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

        .form-group input[type="submit"]:hover {
            background-color: #45a049;
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
</head>

<body>

    <div class="navbar">
        <a href="badgeindex.php">Badge Index</a>
        <a href="badgeadd.php">Add New Badge</a>
        <a href="badgeassign.php">Assign Badge</a>
        <a href="badgeStd.php">Student Badge</a>
    </div>

<div class="main">
    <div class="container">
        <h2>Edit Badge</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $_GET['id']; ?>" method="post"
            enctype="multipart/form-data">
            <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo $name; ?>">
                <span class="help-block"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Current Photo:</label><br>
                <?php if (!empty($photo)) : ?>
                <img src="badgeicon/<?php echo $photo; ?>" width="100" height="100" alt="Current Badge Photo">
                <?php else : ?>
                <span>No photo uploaded.</span>
                <?php endif; ?>
            </div>
            <div class="form-group <?php echo (!empty($photo_err)) ? 'has-error' : ''; ?>">
                <label>New Photo:</label>
                <input type="file" name="photo">
                <span class="help-block"><?php echo $photo_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($description_err)) ? 'has-error' : ''; ?>">
                <label>Description:</label>
                <textarea name="description"><?php echo $description; ?></textarea>
                <span class="help-block"><?php echo $description_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" value="Update">
            </div>
        </form>
    </div>
    </div>
</body>

</html>
