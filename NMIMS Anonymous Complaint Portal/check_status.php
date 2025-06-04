<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Complaint Status</title>
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
	    top: 50%;
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

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(221, 221, 221, 0.7);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.9em;
            color: #333;
            transition: all 0.3s ease;
        }

        input[type="text"]:hover {
            border-color: #c1272d; /* Red */
            box-shadow: 0 0 10px rgba(193, 39, 45, 0.3);
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #c1272d; /* Red */
            box-shadow: 0 0 15px rgba(193, 39, 45, 0.4);
        }

        button {
            padding: 10px;
            background: linear-gradient(135deg, rgba(193, 39, 45, 0.9), rgba(160, 32, 37, 0.9)); /* Red gradient */
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(193, 39, 45, 0.5);
        }

        #statusResult {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .comment {
            background-color: #e8f4f8;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-style: italic;
        }

        .comment::before {
            content: "💬 ";
        }

        /* Progress Line Styles */
        .status-progress {
            margin: 20px 0;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .progress-track {
            height: 6px;
            background: #eee;
            border-radius: 3px;
            margin: 20px 0;
            position: relative;
            overflow: hidden;
        }

        .progress-bar {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            background: #c1272d;
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 20px;
        }

        .step {
            position: relative;
            text-align: center;
            flex: 1;
        }

        .step-dot {
            width: 20px;
            height: 20px;
            background: white;
            border: 3px solid #ddd;
            border-radius: 50%;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .step.active .step-dot {
            background: #c1272d;
            border-color: #c1272d;
        }

        .step.completed .step-dot {
            background: #c1272d;
            border-color: #c1272d;
        }

        .step.completed .step-dot:after {
            content: '✓';
            color: white;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
        }

        .step-label {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
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
        }
    </style>
</head>
<body>
    <!-- Loader -->
    <div class="loader" id="loader">
        <div class="logo-container">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS Logo" class="logo-img"> <!-- Replace with actual logo path -->
            <div class="logo-text">NMIMS</div>
            <div class="logo-subtext">Anonymous Complaint Portal</div>
        </div>
    </div>

    <!-- Glowing Cursor -->
    <div class="cursor-glow" id="cursorGlow"></div>

    <!-- Navigation -->
    <div class="navbar">
        <a href="#" class="navbar-brand">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS Logo"> <!-- Replace with actual logo path -->
        </a>
        <div class="navbar-buttons">
            <a href="check_status.php">Check Status</a>
            <a href="index.php">Complaint</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- University Logo in Center -->
        <div class="university-logo">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS University" height="140" width="200">
        </div>
        
        <header>
            <h1>Check Complaint Status</h1>
            <p>Enter your complaint reference number to check the current status.</p>
        </header>
        <hr color="red"/>
        <br>
        <!-- <h1>Check Complaint Status</h1> -->
        <form id="statusForm" method="POST">
            <div class="form-group">
                <label for="referenceNumber">Enter Reference Number:</label>
                <input type="text" id="referenceNumber" name="referenceNumber" required placeholder="e.g., STM1712234567123">
            </div>
            <button type="submit">Check Status</button>
        </form>
        <div id="statusResult" style="margin-top: 20px;">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Sanitize input
                $referenceNumber = filter_input(INPUT_POST, 'referenceNumber', FILTER_SANITIZE_STRING);
                if (!$referenceNumber) {
                    echo "<p class='error'>Invalid reference number.</p>";
                    exit();
                }

                // Database Credentials
                $host = "localhost";
                $user = "root";
                $password = "";

                // Mapping of school prefixes to databases and escalation levels to tables
                $schoolPrefixes = ["STM" => "stme", "SBM" => "sbm", "SOL" => "sol", "SPT" => "sptm"];
                $escalationTables = [
                    "program_chair" => "program_chair_complaints",
                    "deputy_registrar" => "deputy_registrar_complaints",
                    "campus_director" => "campus_director_complaints"
                ];

                // Extract prefix from reference number (first 3 characters)
                $prefix = strtoupper(substr($referenceNumber, 0, 3));
                $database = null;
                $tables = [];

                // Determine database and tables to check
                if (array_key_exists($prefix, $schoolPrefixes)) {
                    $database = $schoolPrefixes[$prefix];
                    // Check all possible tables for this school
                    $tables = [
                        $escalationTables["program_chair"],
                        $escalationTables["deputy_registrar"]
                    ];
                } elseif ($prefix === "CAM") {
                    $database = "campus_director";
                    $tables = [$escalationTables["campus_director"]];
                } else {
                    echo "<p class='error'>Invalid reference number prefix.</p>";
                    exit();
                }

                // Connect to the determined database
                $conn = new mysqli($host, $user, $password, $database);
                if ($conn->connect_error) {
                    echo "<p class='error'>Connection failed: " . $conn->connect_error . "</p>";
                    exit();
                }

                $status = null;
                $comment = null;
                $found = false;

                // Check each table for the reference number
                foreach ($tables as $table) {
                    $sql = "SELECT status, comment FROM $table WHERE reference_number = ?";
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        continue; // Skip to next table if prepare fails
                    }

                    $stmt->bind_param("s", $referenceNumber);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        continue; // Skip to next table if execute fails
                    }

                    $stmt->bind_result($status, $comment);
                    if ($stmt->fetch()) {
                        $found = true;
                        $stmt->close();
                        break; // Found the reference number, no need to check other tables
                    }
                    $stmt->close();
                }

                if ($found) {
                    // Status display
                    echo "<div class='success' style='padding: 15px; margin-bottom: 20px;'>";
                    echo "<strong>Complaint Status:</strong> " . htmlspecialchars($status ?: "Pending");
                    echo "</div>";

                    // Display comment if exists
                    if (!empty($comment)) {
                        echo "<div class='comment' style='background-color: #e8f4f8; color: #0c5460; border: 1px solid #bee5eb; padding: 15px; border-radius: 8px; margin-top: 10px; font-style: italic;'>";
                        echo "<strong>Comment:</strong> " . htmlspecialchars($comment);
                        echo "</div>";
                    }

                    // Progress line implementation
                    echo "<div class='status-progress' style='background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
                    
                    // Calculate progress width based on status
                    $progressWidth = 0;
                    $status = strtolower($status);
                    if (strpos($status, 'resolved') !== false || strpos($status, 'completed') !== false) {
                        $progressWidth = 100;
                    } elseif (strpos($status, 'progress') !== false || strpos($status, 'processing') !== false) {
                        $progressWidth = 50;
                    } else {
                        $progressWidth = 0;
                    }
                    
                    // Progress track
                    echo "<div class='progress-track' style='height: 6px; background: #eee; border-radius: 3px; margin: 20px 0; position: relative; overflow: hidden;'>";
                    echo "<div class='progress-bar' style='position: absolute; left: 0; top: 0; height: 100%; background: #c1272d; border-radius: 3px; width: " . $progressWidth . "%;'></div>";
                    echo "</div>";
                    
                    // Progress steps
                    echo "<div class='progress-steps' style='display: flex; justify-content: space-between; position: relative; margin-top: 20px;'>";
                    
                    // Step 1: Submitted
                    echo "<div class='step completed' style='position: relative; text-align: center; flex: 1;'>";
                    echo "<div class='step-dot' style='width: 20px; height: 20px; background: #c1272d; border: 3px solid #c1272d; border-radius: 50%; margin: 0 auto; position: relative; z-index: 1;'></div>";
                    echo "<div class='step-label' style='margin-top: 8px; font-size: 14px; color: #666;'>Submitted</div>";
                    echo "</div>";
                    
                    // Step 2: In Progress
                    $step2Class = $progressWidth >= 50 ? 'completed' : '';
                    echo "<div class='step " . $step2Class . "' style='position: relative; text-align: center; flex: 1;'>";
                    echo "<div class='step-dot' style='width: 20px; height: 20px; background: " . ($progressWidth >= 50 ? '#c1272d' : 'white') . "; border: 3px solid " . ($progressWidth >= 50 ? '#c1272d' : '#ddd') . "; border-radius: 50%; margin: 0 auto; position: relative; z-index: 1;'></div>";
                    echo "<div class='step-label' style='margin-top: 8px; font-size: 14px; color: #666;'>In Progress</div>";
                    echo "</div>";
                    
                    // Step 3: Resolved
                    $step3Class = $progressWidth == 100 ? 'completed' : '';
                    echo "<div class='step " . $step3Class . "' style='position: relative; text-align: center; flex: 1;'>";
                    echo "<div class='step-dot' style='width: 20px; height: 20px; background: " . ($progressWidth == 100 ? '#c1272d' : 'white') . "; border: 3px solid " . ($progressWidth == 100 ? '#c1272d' : '#ddd') . "; border-radius: 50%; margin: 0 auto; position: relative; z-index: 1;'></div>";
                    echo "<div class='step-label' style='margin-top: 8px; font-size: 14px; color: #666;'>Resolved</div>";
                    echo "</div>";
                    
                    echo "</div>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>Reference number not found in any table.</div>";
                }

                $conn->close();
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <!-- <div class="footer-container">
        <p>2025 &copy; STME All rights reserved.</p>
    </div> -->

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
    </script>
</body>
</html>