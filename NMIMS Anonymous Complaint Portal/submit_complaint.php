<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaint Submission</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        html, body {
            height: 100%;
            width: 100%;
            background: #f5f5f5; /* Light base color */
            color: #333;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Pattern with Transparency */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(135deg, rgba(255, 255, 255, 0.5) 25%, transparent 25%),
                              linear-gradient(225deg, rgba(255, 255, 255, 0.5) 25%, transparent 25%),
                              linear-gradient(315deg, rgba(255, 255, 255, 0.5) 25%, transparent 25%),
                              linear-gradient(45deg, rgba(255, 255, 255, 0.5) 25%, transparent 25%);
            background-size: 60px 60px;
            background-color: rgba(245, 245, 245, 0.8);
            z-index: -1;
            opacity: 0.4;
            animation: subtleMove 20s infinite linear;
        }

        /* Loader Styles */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f5f5f5; /* Matches body background */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 10000;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .logo-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-img {
            width: 150px;
            height: auto;
            opacity: 0;
            animation: logoFadeIn 1.5s ease forwards;
        }

        .logo-text {
            font-size: 2.5em;
            margin-top: 20px;
            opacity: 0;
            letter-spacing: 5px;
            animation: textSlideIn 1.5s ease forwards 0.5s;
            text-shadow: 0 0 10px rgba(193, 39, 45, 0.7); /* Red glow */
            color: #c1272d; /* Red */
        }

        .logo-subtext {
            font-size: 1em;
            opacity: 0;
            animation: textSlideIn 1.5s ease forwards 0.7s;
            color: #555;
        }

        /* Glowing Cursor Effect */
        .cursor-glow {
            position: fixed;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(193, 39, 45, 0.3) 0%, rgba(193, 39, 45, 0) 70%);
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease;
            transform: translate(-50%, -50%);
        }

        /* Navigation */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(135deg, rgba(193, 39, 45, 0.85), rgba(160, 32, 37, 0.85)); /* Red gradient */
            backdrop-filter: blur(5px);
            padding: 15px 30px;
            z-index: 1000;
            border-bottom: 1px solid rgba(193, 39, 45, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.5s ease-out;
        }

        .navbar-brand img {
            height: 50px;
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.1);
        }

        .navbar-buttons {
            display: flex;
            gap: 10px;
        }

        .navbar-buttons a {
            padding: 10px 20px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            border-radius: 25px;
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .navbar-buttons a:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.4), rgba(255, 255, 255, 0.2));
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(193, 39, 45, 0.5);
        }

        /* Container (Centered) */
        .container {
            width: 50%;
            margin: 80px auto 20px auto; /* Space for navbar and footer */
            text-align: center;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(193, 39, 45, 0.2);
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            z-index: 100;
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        h1 {
            font-size: 1.8em;
            color: #c1272d; /* Red */
            text-shadow: 0 0 10px rgba(193, 39, 45, 0.7);
            animation: neonPulse 2s infinite;
            margin-bottom: 20px;
        }

        p {
            color: #555;
            margin-bottom: 20px;
        }

        .reference-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .copy-btn {
            padding: 8px 15px;
            background: linear-gradient(135deg, rgba(193, 39, 45, 0.9), rgba(160, 32, 37, 0.9)); /* Red gradient */
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: bold;
            transition: all 0.3s ease;
            position: relative;
        }

        .copy-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(193, 39, 45, 0.5);
        }

        .copy-btn::after {
            content: 'Copy';
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.8em;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .copy-btn.copied::after {
            content: 'Copied!';
            opacity: 1;
        }

        /* Footer */
        .footer-container {
            position: fixed;
            bottom: 0;
            width: 100%;
            background: linear-gradient(135deg, rgba(99, 102, 106, 0.9), rgba(74, 78, 82, 0.9)); /* Gray */
            backdrop-filter: blur(5px);
            padding: 15px 0;
            text-align: center;
            color: #ffffff;
            font-size: 0.9em;
            border-top: 1px solid rgba(193, 39, 45, 0.3);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            animation: slideUp 0.5s ease-out;
            z-index: 1000;
        }

        /* Animations */
        @keyframes logoFadeIn {
            0% { opacity: 0; transform: scale(0.5); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes textSlideIn {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        @keyframes neonPulse {
            0%, 100% { text-shadow: 0 0 10px rgba(193, 39, 45, 0.7); }
            50% { text-shadow: 0 0 20px rgba(193, 39, 45, 1); }
        }

        @keyframes subtleMove {
            0% { background-position: 0 0; }
            100% { background-position: 60px 60px; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                margin: 60px auto 20px auto;
                padding: 20px;
            }

            h1 {
                font-size: 1.5em;
            }

            .navbar {
                padding: 10px 15px;
            }

            .navbar-brand img {
                height: 40px;
            }

            .navbar-buttons a {
                padding: 8px 15px;
                font-size: 0.9em;
            }

            .footer-container {
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader" id="loader">
        <div class="logo-container">
            <img src="nmims-logo.png" alt="NMIMS Logo" class="logo-img"> <!-- Replace with actual logo path -->
            <div class="logo-text">NMIMS</div>
            <div class="logo-subtext">Anonymous Complaint Portal</div>
        </div>
    </div>

    <!-- Glowing Cursor -->
    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- Navigation -->
    <div class="navbar">
        <a href="#" class="navbar-brand">
            <img src="nmims-logo.png" alt="NMIMS Logo"> <!-- Replace with actual logo path -->
        </a>
        <div class="navbar-buttons">
            <a href="check_status.php">Check Status</a>
            <a href="index.php">Complaint</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <h1>Complaint Submission</h1>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and validate inputs
            $school = filter_input(INPUT_POST, 'school', FILTER_SANITIZE_STRING);
            $complaintType = filter_input(INPUT_POST, 'complaintType', FILTER_SANITIZE_STRING);
            $escalation = filter_input(INPUT_POST, 'escalation', FILTER_SANITIZE_STRING);
            $complaintDetails = filter_input(INPUT_POST, 'complaintDetails', FILTER_SANITIZE_STRING);

            if (!$school || !$complaintType || !$escalation || !$complaintDetails) {
                echo "<p style='color: #721c24;'>Invalid input data.</p>";
            } else {
                // Database Credentials
                $host = "localhost";
                $user = "root";
                $password = "";
                $database = $school; // Default database is the school

                // Mapping of school names to prefixes (reverse of check_status.php mapping)
                $schoolPrefixes = [
                    "stme" => "STM",
                    "sbm" => "SBM",
                    "sol" => "SOL",
                    "sptm" => "SPT"
                ];

                // Determine the correct table and database for escalation
                if ($escalation === "program_chair") {
                    $table = "program_chair_complaints";
                } elseif ($escalation === "deputy_registrar") {
                    $table = "deputy_registrar_complaints";
                } elseif ($escalation === "campus_director") {
                    $database = "campus_director"; // Override database for Campus Director
                    $table = "campus_director_complaints";
                } elseif ($escalation === "hostel_rector") {
                    $database = "hostel_rector"; // Override database for Hostel Rector
                    $table = "hostel_rector_complaints";
                } else {
                    echo "<p style='color: #721c24;'>Invalid escalation level.</p>";
                    exit();
                }

                // Create Database Connection
                $conn = new mysqli($host, $user, $password, $database);
                if ($conn->connect_error) {
                    echo "<p style='color: #721c24;'>Connection failed: " . $conn->connect_error . "</p>";
                    exit();
                }

                // Generate Unique Reference Number based on escalation level
                $prefix = "";
                if ($escalation === "campus_director") {
                    $prefix = "CAM"; // Use CAM for campus_director
                } elseif ($escalation === "hostel_rector") {
                    $prefix = "HOS"; // Use HOS for hostel_rector
                } else {
                    // Use the school prefix for program_chair and deputy_registrar
                    if (array_key_exists($school, $schoolPrefixes)) {
                        $prefix = $schoolPrefixes[$school];
                    } else {
                        echo "<p style='color: #721c24;'>Invalid school name.</p>";
                        $conn->close();
                        exit();
                    }
                }

                // Generate the reference number: PREFIX + TIMESTAMP + RANDOM
                $referenceNumber = $prefix . time() . rand(100, 999);

                // Insert into the respective table
                $sql = "INSERT INTO $table (reference_number, complaint_details) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "<p style='color: #721c24;'>Prepare failed: " . $conn->error . "</p>";
                    $conn->close();
                    exit();
                }

                $stmt->bind_param("ss", $referenceNumber, $complaintDetails);

                if ($stmt->execute()) {
                    echo "<p style='color: #155724;'>Complaint submitted successfully! Your reference number is: <strong id='referenceNumber'>$referenceNumber</strong></p>";
                    echo "<div class='reference-container'>";
                    echo "<button class='copy-btn' onclick='copyToClipboard()'>Copy to Clipboard</button>";
                    echo "</div>";
                } else {
                    echo "<p style='color: #721c24;'>Error submitting complaint: " . $stmt->error . "</p>";
                }

                $stmt->close();
                $conn->close();
            }
        } else {
            echo "<p style='color: #721c24;'>Invalid request method.</p>";
        }
        ?>
    </div>

    <!-- Footer -->
    <div class="footer-container">
        <p>2025 &copy; STME All rights reserved.</p>
    </div>

    <script>
        // Loader Hide
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
            }, 2000);
        });

        // Glowing Cursor
        const cursorGlow = document.getElementById('cursorGlow');
        document.addEventListener('mousemove', (e) => {
            cursorGlow.style.left = e.pageX + 'px';
            cursorGlow.style.top = e.pageY + 'px';
        });

        // Copy to Clipboard Function
        function copyToClipboard() {
            const referenceNumber = document.getElementById('referenceNumber').innerText;
            navigator.clipboard.writeText(referenceNumber).then(() => {
                const copyBtn = document.querySelector('.copy-btn');
                copyBtn.classList.add('copied');
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</body>
</html>