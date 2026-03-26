<?php
session_start();
require_once 'config/database.php';
require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Support\Csrf;

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NMIMS University Anonymous Complaint Portal</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=20260326b">
    <link rel="stylesheet" href="assets/css/app.css?v=20260326b">
</head>
<body class="public-page">
    <header class="site-header">
        <nav class="navbar">
            <div class="navbar-logo">
                <img src="assets/nmims_logo.jpg" alt="NMIMS Logo">
            </div>
            <div class="nav-links">
                <div class="check-status">
                    <a href="check-status/check-status.php">Check Status</a>
                </div>
                <div class="admin-login">
                    <a href="admin/login.php">Admin Login</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="container">
        <!-- University Logo in Center -->
        <div class="university-logo">
            <img src="assets/nmims_logo.jpg" alt="NMIMS University">
        </div>
        
        <header>
            <h1>NMIMS Anonymous Complaint Portal</h1>
            <p>Submit your concerns anonymously. Your feedback helps us improve.</p>
        </header>
        
        <form id="complaintForm" action="submit_complaint.php" method="POST">
            <?php
            $csrfToken = Csrf::generateToken($_SESSION, 'complaint_submit');
            ?>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <div id="formSummary" class="form-summary" role="alert" aria-live="assertive" tabindex="-1" hidden></div>

            <div class="form-group">
                <label for="school">Select School:</label>
                <select id="school" name="school" required aria-required="true">
                    <option value="" selected disabled>Choose a school</option>
                    <?php
                    $sql = "SELECT school_id, school_name, max_degree_year FROM schools ORDER BY school_name";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<option value='" . (int) $row['school_id'] . "' data-max-year='" . (int) $row['max_degree_year'] . "'>" . htmlspecialchars($row['school_name']) . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            
            <div class="form-group">
                <label for="complaintType">Complaint Related To:</label>
                <select id="complaintType" name="complaintType" required aria-required="true">
                    <option value="" selected disabled>Select complaint type</option>
                    <option value="academic">Academic</option>
                    <option value="hostel">Hostel</option>
                </select>
            </div>
            
            <!-- Academic Subtype -->
            <div class="form-group" id="academicSubTypeContainer">
                <label for="academicSubType">Academic Issue Type:</label>
                <select id="academicSubType" name="academicSubType">
                    <option value="" selected disabled>Select specific issue</option>
                    <option value="professor">Professor/Faculty</option>
                    <option value="curriculum">Curriculum</option>
                    <option value="examination">Examination</option>
                    <option value="hospitality">Hospitality</option>
                    <option value="housekeeping">Housekeeping</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <!-- Hostel Subtype -->
            <div class="form-group" id="hostelSubTypeContainer">
                <label for="hostelSubType">Hostel Issue Type:</label>
                <select id="hostelSubType" name="hostelSubType">
                    <option value="" selected disabled>Select specific issue</option>
                    <option value="food">Food/Mess</option>
                    <option value="housekeeping">Housekeeping</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="hospitality">Hospitality</option>
                    <option value="roommate">Roommate</option>
                    <option value="security">Security</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Hostel Authority (Checkbox) -->
            <div class="form-group" id="hostelContainer">
                <fieldset>
                    <legend>Select Hostel Authority:</legend>
                    <div id="hostelAuthorityCheckboxes" role="group" aria-label="Hostel authority list">
                    <?php
                    $sql = "SELECT user_id, name FROM users WHERE role = 'hostel_authority'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<label><input type='checkbox' name='hostelAuthority[]' value='" . $row['user_id'] . "'> " . $row['name'] . "</label>";
                        }
                    }
                    ?>
                    </div>
                </fieldset>
            </div>
            
            <!-- Escalation -->
            <div class="form-group" id="escalationContainer">
                <label for="escalation">Submit this complaint to:</label>
                <select id="escalation" name="escalation" required aria-required="true">
                    <option value="" selected disabled>Select authority</option>
                    <option value="programChair">Program Chair</option>
                    <option value="deputyRegistrar">Deputy Registrar</option>
                    <option value="campusDirector">Campus Director</option>
                </select>
            </div>
            
            <!-- Program Chair (Checkbox) -->
            <div class="form-group" id="programChairContainer">
                <fieldset>
                    <legend>Select Program Chair (Optional Additional Recipient):</legend>
                    <div id="programChairCheckboxes" role="group" aria-label="Program chair list">
                    <?php
                    $sql = "SELECT user_id, name, school_id FROM users WHERE role = 'program_chair' AND is_active = 1 ORDER BY name";
                    $result = $conn->query($sql);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<label class='program-chair-option' data-school-id='" . (int) $row['school_id'] . "'>";
                            echo "<input type='checkbox' name='programChair[]' value='" . (int) $row['user_id'] . "'> " . htmlspecialchars($row['name']);
                            echo "</label>";
                        }
                    }
                    ?>
                    </div>
                </fieldset>
            </div>

            <!-- Complaint Details -->
            <div class="form-group">
                <label for="complaintDetails">Complaint Details:</label>
                <textarea id="complaintDetails" name="complaintDetails" rows="5" required aria-required="true" placeholder="Please describe your issue in detail..."></textarea>
            </div>
            
            <!-- Anonymous Checkbox -->
            <div class="form-group anonymous-checkbox">
                <input type="checkbox" id="anonymousCheck" name="anonymousCheck" checked="checked">
                <label for="anonymousCheck">Keep my identity anonymous</label>
            </div>
            <small id="identityRuleHint" class="identity-hint">
                If unchecked, Name, SAP ID, and Year are mandatory to submit.
            </small>

            <div class="form-group" id="studentIdentityContainer">
                <label for="studentName">Name:</label>
                <input type="text" id="studentName" name="studentName" maxlength="120" placeholder="Enter your name">

                <label for="sapId" class="inline-label-spaced">SAP ID (Student ID):</label>
                <input type="text" id="sapId" name="sapId" maxlength="30" placeholder="Enter your student SAP ID">

                <label for="studyYear" class="inline-label-spaced">Year:</label>
                <select id="studyYear" name="studyYear">
                    <option value="" selected>Select year</option>
                </select>
                <small id="yearHint" class="muted-helper"></small>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" id="submitBtn">Submit Complaint</button>
            </div>
        </form>
        
        <!-- Confirmation Message -->
        <div id="confirmationMessage">
            <h2>Thank You</h2>
            <p>Your complaint has been submitted anonymously.<br/> Your reference number is: <span id="referenceNumber"></span></p>
            <button type="button" id="copyReferenceBtn">Copy Reference Number</button>
            <p id="copyStatus" aria-live="polite"></p>
            <p>You can use this reference number to check the status of your complaint later.</p>
            <div class="check-status">
                <a href="check-status/check-status.php">Check Complaint Status</a>
            </div>
            <button id="newComplaintBtn">Submit Another Complaint</button>
        </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <strong>NMIMS Anonymous Complaint Portal</strong>
                <span>Secure reporting for students across schools and authorities.</span>
            </div>
            <div class="footer-links">
                <a href="check-status/check-status.php">Check Status</a>
                <a href="admin/login.php">Admin Login</a>
                <a href="about-portal.php">About Portal</a>
            </div>
            <div class="footer-copy">
                <span>2026 &copy; STME. All rights reserved.</span>
            </div>
        </div>
    </footer>
    
    <script src="assets/js/app.js"></script>
</body>
</html> 