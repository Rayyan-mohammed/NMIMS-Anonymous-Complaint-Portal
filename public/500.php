<?php
$scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
$publicBase = '/public';
$markerPos = strpos($scriptName, '/public/');
if ($markerPos !== false) {
    $publicBase = substr($scriptName, 0, $markerPos + 7);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something Went Wrong</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($publicBase); ?>/assets/css/theme.css?v=20260325b">
    <style>
        .wrap { max-width: 640px; text-align: center; }
        img { max-width: 180px; height: auto; margin-bottom: 16px; filter: drop-shadow(0 8px 15px rgba(20, 33, 48, 0.18)); }
        h1 { margin: 0 0 10px; color: #8f162a; }
        p { color: #4a5a6a; }
        a { color: #0e5a66; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="wrap">
        <img src="<?php echo htmlspecialchars($publicBase); ?>/assets/nmims_logo.jpg" alt="NMIMS Logo">
        <h1>We hit an unexpected issue</h1>
        <p>Please try again in a moment. If this continues, contact the administrator.</p>
        <p><a href="<?php echo htmlspecialchars($publicBase); ?>/index.php">Go back to home</a></p>
    </div>
</body>
</html>
