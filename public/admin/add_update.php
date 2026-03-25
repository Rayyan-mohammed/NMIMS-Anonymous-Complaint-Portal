<?php
session_start();
require_once '../config/database.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Http\Controllers\Admin\ComplaintController;

$controller = new ComplaintController($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit();
}

$redirect = $controller->handleAddUpdate($_SESSION, $_POST);
header('Location: ' . $redirect);
exit();