<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMIMS Anonymous Complaint Portal</title>
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

        header {
            margin-bottom: 20px;
        }

        h1 {
            font-size: 1.8em;
            color: #c1272d; /* Red */
            text-shadow: 0 0 10px rgba(193, 39, 45, 0.7);
            animation: neonPulse 2s infinite;
        }

        p {
            color: #555;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left; /* Align form elements to the left */
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(221, 221, 221, 0.7);
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.8);
            font-size: 0.9em;
            color: #333;
            transition: all 0.3s ease;
        }

        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23c1272d" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        select:hover, textarea:hover {
            border-color: #c1272d; /* Red */
            box-shadow: 0 0 10px rgba(193, 39, 45, 0.3);
        }

        select:focus, textarea:focus {
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
            <a href="admin.php">Admin</a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container">
        <!-- University Logo in Center -->
        <div class="university-logo">
            <img src="https://iili.io/3K16L6F.md.jpg" alt="NMIMS University" height="200" width="400">
        </div>

        <header>
            <h1>NMIMS Anonymous Complaint Portal</h1>
            <p>Submit your concerns anonymously. Your feedback helps us improve.</p>
        </header>
        
        <hr color="red"/>
        <br>

        <!-- Complaint Form -->
        <form id="complaintForm" action="submit_complaint.php" method="POST">
            <div class="form-group">
                <label for="school">Select School:</label>
                <select id="school" name="school" required>
                    <option value="" selected disabled>Choose a school</option>
                    <option value="stme">STME</option>
                    <option value="sbm">SBM</option>
                    <option value="sol">SOL</option>
                    <option value="sptm">SPTM</option>
                </select>
            </div>

            <div class="form-group">
                <label for="complaintType">Complaint Related To:</label>
                <select id="complaintType" name="complaintType" required onchange="updateEscalationOptions()">
                    <option value="" selected disabled>Select complaint type</option>
                    <option value="academic">Academic</option>
                    <option value="hostel">Hostel</option>
                </select>
            </div>

            <div class="form-group">
                <label for="escalation">Submit this complaint to:</label>
                <select id="escalation" name="escalation" required>
                    <option value="" selected disabled>Select authority</option>
                </select>
            </div>

            <div class="form-group">
                <label for="complaintDetails">Complaint Details:</label>
                <textarea id="complaintDetails" name="complaintDetails" rows="5" required placeholder="Describe your issue..."></textarea>
            </div>

            <button type="submit">Submit Complaint</button>
        </form>
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

        // Update Escalation Options based on Complaint Type
        function updateEscalationOptions() {
            const complaintType = document.getElementById('complaintType').value;
            const escalationSelect = document.getElementById('escalation');
            
            // Clear existing options
            escalationSelect.innerHTML = '<option value="" selected disabled>Select authority</option>';
            
            if (complaintType === 'academic') {
                const academicOptions = [
                    { value: 'program_chair', text: 'Program Chair' },
                    { value: 'deputy_registrar', text: 'Deputy Registrar' },
                    { value: 'campus_director', text: 'Campus Director' }
                ];
                academicOptions.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    escalationSelect.appendChild(opt);
                });
            } else if (complaintType === 'hostel') {
                const hostelOptions = [
                    { value: 'hostel_rector', text: 'Hostel Rector' },
                    { value: 'deputy_registrar', text: 'Deputy Registrar' },
                    { value: 'campus_director', text: 'Campus Director' }
                ];
                hostelOptions.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    escalationSelect.appendChild(opt);
                });
            }
        }
    </script>
</body>
</html>