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
    <link rel="stylesheet" href="../assets/css/admin/login.css?v=20260326a">
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
            <div class="table-wrap">
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
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
