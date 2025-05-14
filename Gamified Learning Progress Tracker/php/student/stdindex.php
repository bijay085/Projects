<?php
include 'D:\xampp\htdocs\glptwor\php\co.php';

session_start();

if (!isset($_SESSION['roleid'])) {
    Header('Location: ../register/login.php');
}
$roleid = $_SESSION['roleid'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Index</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            /* Adjust background color transparency here */
            background-image: linear-gradient(rgba(0, 0, 0, 0.9), rgba(0, 0, 0, 0.4)), url('bg.webp');
            background-size: cover;
            background-repeat: no-repeat;
            margin: 0;
            padding: 0;
            transition: background-color 0.5s ease;
            animation: moveBgImage 250s infinite linear;
        }

        @keyframes moveBgImage {
            0% {
                background-position: 0% 0%;
            }

            50% {
                background-position: 100% 0%;
            }

            100% {
                background-position: 0% 0%;
            }
        }

        .hover-stop {
            animation-play-state: paused;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            /* background-color: #dadce0; */
            background-color: #3333;
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

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h1 {
            color: #333;
        }

        .content p {
            color: #666;
        }

        /* Styles for quotes and jokes div */
        .quotesDiv,
        .jokesDiv,
        .bca,
        .whybca,
        .studydiv {
            border-radius: 8px;
            padding: 15px;
            width: 45%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
            font-style: italic;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            font-weight: 15px;
            transition: background-color 0.3s ease;
        }

        .quotesDiv {
            background-color: #dadce0;
            margin: 5px 0px 20px 35px;
            /* top right bottom left */
        }

        .jokesDiv {
            background-color: #dedbd3;
            margin: 15px 0px 0px 43rem;
            /* top right bottom left */
        }

        .bca {
            background-color: #dadce0;
            margin: 5px 0px 20px 35px;
        }

        .whybca {
            background-color: #dedbd3;
            margin: 15px 0px 0px 43rem;
        }

        .studydiv {
            background-color: #dadce0;
            margin: 5px 0px 20px 35px;
        }

        .quotesDiv:hover,
        .studydiv:hover,
        .bca:hover {
            background-color: rgb(107, 194, 172);
            transform: scale(1) rotate(4deg);
            transition: transform 0.3s ease, font-size 0.3s ease;
        }

        .jokesDiv:hover,
        .whybca:hover {
            background-color: rgb(107, 194, 172);
            transform: scale(1) rotate(-4deg);
            transition: transform 0.3s ease, font-size 0.3s ease;
        }

        .quotesDiv:hover h2,
        .studydiv:hover h2,
        .bca:hover h2,
        .jokesDiv:hover h2,
        .whybca:hover h2 {
            font-size: 24px;
            transition: font-size 0.3s ease;
        }

        .content h1,
        .content p {
            color: #dadce0;
        }

        .photo {
            margin-top: 4px;
            height: 180px;
            width: 180px;
            border-radius: 50%;
            object-fit: cover;
            float: right;
            margin-left: 20px;
            background: transparent;
        }

        .hover-text {
            position: absolute;
            top: 50%;
            left: 80%;
            transform: translate(-50%, -50%);
            background-color: gray;
            color: purple;
            padding: 30px;
            border-radius: 5px;
            display: none;
            text-align: center;
        }

        .photo:hover .hover-text {
            display: block;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <a href="stdindex.php" class="active">Home</a>
        <a href="studentDisplayAssignment.php">Assignment</a>
        <a href="profile.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>

    <div class="container">
        <div class="photo">
            <img src="wolf.jpg" alt="wolf" class="photo">
            <div class="hover-text">Have patience my friend !</div>
        </div>
        <div class="content">
            <h1>Welcome to the Student Dashboard</h1>
            <p>You are in the student section.</p>
        </div>

        <!-- Quotes section -->
        <div class="quotesDiv">
            <h2>Motivational Quotes</h2>
            <p id="quote"></p>
        </div>

        <!-- Jokes section -->
        <div class="jokesDiv">
            <h2>Random Jokes</h2>
            <p id="joke"></p>
        </div>

        <!-- About BCA section -->
        <div class="bca">
            <h2>About BCA</h2>
            <p id="bca">Bachelor of Computer Applications (BCA) is a popular undergraduate program in computer
                applications. It is an ideal course for students aiming to pursue a career in software development, IT
                management, and related fields. BCA covers a wide range of subjects including programming languages,
                database management, networking, and software engineering.</p>
        </div>

        <!-- Why BCA section -->
        <div class="whybca">
            <h2>Why to do BCA</h2>
            <p id="wbca">BCA offers a solid foundation in computer applications and software development, preparing
                students for careers in IT and related fields. It covers essential topics like programming, database
                management, and networking, making graduates highly sought after in the tech industry. BCA also provides
                opportunities for practical learning through projects and internships, enhancing job readiness.</p>
        </div>

        <!-- Best Way of Studying section -->
        <div class="studydiv">
            <h2>What is the best way of studying?</h2>
            <ul id="study">
                <li>Create a conducive study environment.</li>
                <li>Stay organized with a study schedule.</li>
                <li>Use active learning strategies like flashcards and mind maps.</li>
                <li>Practice active recall to reinforce learning.</li>
                <li>Take regular breaks and maintain a balanced schedule.</li>
                <li>Get enough rest for optimal focus and retention.</li>
            </ul>
        </div>
    </div>

    <script>
        // Array of quotes
        const quotes = [
            "Success is not final, failure is not fatal: It is the courage to continue that counts. - Winston Churchill",
            "The only limit to our realization of tomorrow will be our doubts of today. - Franklin D. Roosevelt",
            "The way to get started is to quit talking and begin doing. - Walt Disney",
            "Don't cry because it's over, smile because it happened. - Dr. Seuss",
            "Life is what happens when you're busy making other plans. - John Lennon",
            "Believe you can and you're halfway there. - Theodore Roosevelt",
            "The only way to achieve the impossible is to believe it is possible. - Charles Kingsleigh(Alice in Wonderland)",
            "Your limitation—it's only your imagination. - Unknown",
            "Push yourself, because no one else is going to do it for you. - Unknown",
            "The harder you work for something, the greater you'll feel when you achieve it. - Unknown"
        ];

        // Array of jokes
        const jokes = [
            "Why don't scientists trust atoms? Because they make up everything!",
            "I told my wife she should embrace her mistakes. She gave me a hug.",
            "Why did the scarecrow win an award? Because he was outstanding in his field!",
            "I'm reading a book on anti-gravity. It's impossible to put down!",
            "Parallel lines have so much in common. It’s a shame they’ll never meet."
        ];

        // Function to get a random item from an array
        function getRandomItem(array) {
            const randomIndex = Math.floor(Math.random() * array.length);
            return array[randomIndex];
        }

        // Function to update the quote
        function updateQuote() {
            const quoteElement = document.getElementById('quote');
            quoteElement.textContent = getRandomItem(quotes);
        }

        // Function to update the joke
        function updateJoke() {
            const jokeElement = document.getElementById('joke');
            jokeElement.textContent = getRandomItem(jokes);
        }

        // Update quote and joke initially
        updateQuote();
        updateJoke();

        // Update quote every 5 seconds (5000 milliseconds)
        setInterval(updateQuote, 5000);

        // Update joke every 8 seconds (8000 milliseconds)
        setInterval(updateJoke, 8000);

        // Add event listeners to pause background animation on div hover
        document.querySelectorAll('.quotesDiv, .jokesDiv, .bca, .whybca, .studydiv').forEach(item => {
            let timeout;
            item.addEventListener('mouseover', () => {
                timeout = setTimeout(() => {
                    document.body.classList.add('hover-stop');
                }, 500); // 2000 milliseconds = 2 seconds -> 0.5sec
            });

            item.addEventListener('mouseout', () => {
                clearTimeout(timeout);
                document.body.classList.remove('hover-stop');
            });
        });
    </script>
</body>

</html>