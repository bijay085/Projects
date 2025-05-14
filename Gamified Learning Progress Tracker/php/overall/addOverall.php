<?php
// Check if roleid and fullname are provided via GET request
if (isset($_GET['roleid']) && isset($_GET['fullname'])) {
    // Sanitize inputs
    $roleid = $_GET['roleid'];
    $fullname = urldecode($_GET['fullname']); // Decode URL-encoded fullname

    // Display a form for adding overall performance
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Add Overall Performance</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f0f0f0;
                margin: 0;
                padding: 20px;
            }
            .container {
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                padding: 20px;
                width: 50%;
                margin: auto;
            }
            h2 {
                text-align: center;
                margin-bottom: 20px;
                color: #333;
            }
            form {
                display: flex;
                flex-direction: column;
            }
            label {
                font-weight: bold;
                margin-bottom: 10px;
            }
            input[type="text"], textarea {
                padding: 8px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
                font-size: 14px;
            }
            input[type="submit"] {
                background-color: #007bff;
                color: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            input[type="submit"]:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
    <div class="container">
        <h2>Add Overall Performance for <?php echo htmlspecialchars($fullname); ?></h2>
        <form action="processOverall.php" method="POST">
            <input type="hidden" name="roleid" value="<?php echo htmlspecialchars($roleid); ?>">
            
            <label for="attendance">Attendance:</label>
            <select id="attendance" name="attendance" required>
                <option value="full">Full</option>
                <option value="half">Half</option>
                <option value="none">None</option>
            </select>
            
            <label for="assignment">Assignment:</label>
            <select id="assignment" name="assignment" required>
                <option value="submitted">Submitted</option>
                <option value="incomplete">Incomplete</option>
                <option value="not done">Not Done</option>
            </select>
            
            <label for="performance">Performance:</label>
            <select id="performance" name="performance" required>
                <option value="good">Good</option>
                <option value="average">Average</option>
                <option value="bad">Bad</option>
            </select>
            
            <label for="result">Result:</label>
            <select id="result" name="result" required>
                <option value="pass">Pass</option>
                <option value="fail">Fail</option>
                <option value="not attempted">Not Attempted</option>
            </select>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <label for="programming_language">Programming Language:</label>
            <input type="text" id="programming_language" name="programming_language" required>
            
            <label for="class_activity">Class Activity (%):</label>
            <input type="text" id="class_activity" name="class_activity" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">
            
            <label for="performance_rate">Performance Rate (%):</label>
            <input type="text" id="performance_rate" name="performance_rate" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">
            
            <label for="behavior">Behavior (%):</label>
            <input type="text" id="behavior" name="behavior" required pattern="\d+(\.\d{1,2})?" title="Please enter a valid percentage (up to 2 decimal places)">
            
            <label for="overall">Overall (%):</label>
            <input type="text" id="overall" name="overall" readonly>
            
            <input type="submit" value="Add Overall">
        </form>
    </div>
    <script>
        // Calculate overall percentage based on class activity, performance rate, and behavior
        document.querySelector('form').addEventListener('input', function() {
            var classActivity = parseFloat(document.getElementById('class_activity').value) || 0;
            var performanceRate = parseFloat(document.getElementById('performance_rate').value) || 0;
            var behavior = parseFloat(document.getElementById('behavior').value) || 0;

            var overall = (classActivity + performanceRate + behavior) / 3;
            document.getElementById('overall').value = overall.toFixed(2);
        });
    </script>
    </body>
    </html>
    <?php
} else {
    // Redirect back to overallStd.php if roleid or fullname parameters are missing
    header("Location: overallStd.php");
    exit();
}
?>
