<?php
session_start();
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
    <title>About Portal - NMIMS</title>
    <link rel="stylesheet" href="assets/css/theme.css?v=20260326b">
    <link rel="stylesheet" href="assets/css/app.css?v=20260326d">
</head>
<body class="public-page">
    <header class="site-header">
        <nav class="navbar">
            <div class="navbar-logo">
                <img src="assets/nmims_logo.jpg" alt="NMIMS Logo">
            </div>
            <div class="nav-links">
                <div class="check-status">
                    <a href="index.php">Home</a>
                </div>
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
        <div class="container about-shell">
            <section class="about-portal-wrap" aria-labelledby="aboutPortalHeading">
                <div class="about-hero-grid">
                    <div class="about-hero-copy">
                        <span class="about-badge">Safe. Confidential. Actionable.</span>
                        <h1 id="aboutPortalHeading">NMIMS Anonymous Complaint Portal</h1>
                        <p>Submit concerns securely and help us improve academic and hostel life with transparent follow-up.</p>

                        <div class="about-pill-row" aria-label="Portal highlights">
                            <span>Anonymous option</span>
                            <span>Reference-based tracking</span>
                            <span>Role-based resolution workflow</span>
                        </div>
                    </div>

                    <aside class="about-quick-card" aria-label="Quick actions">
                        <h2>Quick Access</h2>
                        <p>Reach key actions quickly without searching through the full site.</p>
                        <div class="about-cta-row">
                            <a href="check-status/check-status.php">Check Existing Status</a>
                            <a href="admin/login.php">Authority Sign-In</a>
                        </div>
                    </aside>
                </div>

                <div class="about-detail-grid">
                    <article class="about-flow-card" aria-labelledby="howItWorksHeading">
                        <h2 id="howItWorksHeading">How It Works</h2>
                        <ol>
                            <li>Fill details and choose complaint category.</li>
                            <li>Keep identity anonymous or share student details.</li>
                            <li>Get a reference number to track updates.</li>
                        </ol>
                    </article>

                    <article class="about-flow-card about-assurance-card" aria-label="Portal assurance">
                        <h2>Why This Portal Exists</h2>
                        <p>The portal is designed to give students a secure, respectful channel for concerns while ensuring the right authority can respond with accountability and transparency.</p>
                    </article>
                </div>
            </section>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <strong>NMIMS Anonymous Complaint Portal</strong>
                <span>Secure reporting for students across schools and authorities.</span>
            </div>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="check-status/check-status.php">Check Status</a>
                <a href="admin/login.php">Admin Login</a>
            </div>
            <div class="footer-copy">
                <span>2026 &copy; STME. All rights reserved.</span>
            </div>
        </div>
    </footer>
</body>
</html>
