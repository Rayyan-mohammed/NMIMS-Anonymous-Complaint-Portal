<?php
session_start();
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\AuthGuard;

$redirect = AuthGuard::requireRole($_SESSION, ['program_chair', 'deputy_registrar', 'hostel_authority', 'campus_director']);
if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
}

$backupDir = dirname(__DIR__, 2) . '/storage/backups';
$archives = [];

if (is_dir($backupDir)) {
    $backupFiles = glob($backupDir . '/apnm6_backup_*.zip');
    if ($backupFiles === false || count($backupFiles) === 0) {
        $backupFiles = glob($backupDir . '/*.zip') ?: [];
    }

    foreach ($backupFiles as $filePath) {
        if (!is_file($filePath)) {
            continue;
        }

        $archives[] = [
            'name' => basename($filePath),
            'path' => $filePath,
            'size' => filesize($filePath) ?: 0,
            'modified' => filemtime($filePath) ?: 0,
        ];
    }

    usort($archives, static function (array $a, array $b): int {
        return $b['modified'] <=> $a['modified'];
    });
}

$latestArchive = $archives[0] ?? null;

function formatBytes(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $power = (int) floor(log($bytes, 1024));
    $power = min($power, count($units) - 1);
    $scaled = $bytes / (1024 ** $power);

    return number_format($scaled, $power === 0 ? 0 : 2) . ' ' . $units[$power];
}

function formatDateTime(int $timestamp): string
{
    if ($timestamp <= 0) {
        return '-';
    }

    return date('d M Y H:i:s', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Health - NMIMS Complaint Portal</title>
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260325b">
    <style>
        .backup-container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid rgba(14, 90, 102, 0.22);
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,253,0.9));
        }
        .brand-logo {
            margin-bottom: 10px;
        }
        .brand-logo img {
            max-width: 220px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 8px 18px rgba(24, 42, 62, 0.2));
        }
        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(180deg, #0e6d82, #0b5768);
            color: white;
            text-decoration: none;
            border-radius: 999px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 12px;
            margin-bottom: 22px;
        }
        .summary-card {
            border: 1px solid rgba(31, 41, 51, 0.12);
            border-radius: 12px;
            padding: 12px;
            background: rgba(255,255,255,0.92);
        }
        .summary-card .label {
            color: #4e6177;
            font-size: 0.92rem;
            margin-bottom: 6px;
            display: block;
        }
        .summary-card .value {
            color: #123249;
            font-weight: 700;
            font-size: 1rem;
        }
        .health-ok {
            color: #1f7d4d;
            font-weight: 700;
        }
        .health-warn {
            color: #9c2d21;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            background: rgba(255,255,255,0.9);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid rgba(31, 41, 51, 0.12);
        }
        th {
            background: rgba(14, 90, 102, 0.1);
            color: #113e4f;
        }
        .empty-state {
            margin-top: 16px;
            padding: 14px;
            border: 1px solid rgba(179, 27, 52, 0.24);
            border-radius: 10px;
            background: rgba(179, 27, 52, 0.08);
            color: #7f1c2f;
        }
    </style>
</head>
<body>
    <div class="backup-container">
        <div class="brand-logo">
            <img src="../assets/nmims_logo.jpg" alt="NMIMS University Logo">
        </div>

        <a href="dashboard.php" class="back-btn">Back to Dashboard</a>

        <h2>Backup Health</h2>
        <p>Latest backup archive timestamp and size from storage/backups.</p>

        <div class="summary-grid">
            <div class="summary-card">
                <span class="label">Health Status</span>
                <span class="value <?php echo $latestArchive ? 'health-ok' : 'health-warn'; ?>">
                    <?php echo $latestArchive ? 'Healthy (backup found)' : 'Attention needed'; ?>
                </span>
            </div>
            <div class="summary-card">
                <span class="label">Latest Archive</span>
                <span class="value"><?php echo htmlspecialchars((string) ($latestArchive['name'] ?? 'No archive found')); ?></span>
            </div>
            <div class="summary-card">
                <span class="label">Latest Timestamp</span>
                <span class="value"><?php echo formatDateTime((int) ($latestArchive['modified'] ?? 0)); ?></span>
            </div>
            <div class="summary-card">
                <span class="label">Latest Size</span>
                <span class="value"><?php echo formatBytes((int) ($latestArchive['size'] ?? 0)); ?></span>
            </div>
        </div>

        <h3>Recent Backups</h3>
        <?php if (empty($archives)): ?>
            <div class="empty-state">
                No backup ZIP files found in <?php echo htmlspecialchars($backupDir); ?>.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Archive</th>
                        <th>Timestamp</th>
                        <th>Size</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($archives, 0, 10) as $archive): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string) $archive['name']); ?></td>
                            <td><?php echo htmlspecialchars(formatDateTime((int) $archive['modified'])); ?></td>
                            <td><?php echo htmlspecialchars(formatBytes((int) $archive['size'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
