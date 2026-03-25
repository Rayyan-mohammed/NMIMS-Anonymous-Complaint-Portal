<?php
session_start();
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Support\AuthGuard;

$redirect = AuthGuard::requireRole($_SESSION, ['campus_director']);
if ($redirect !== null) {
    header('Location: ' . $redirect);
    exit();
}

header('Location: dashboard.php');
exit();
