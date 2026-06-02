<?php
require_once __DIR__ . '/includes/init.php';

use App\Service\AuthService;

$auth = new AuthService();
$auth->logout();
header('Location: /login.php');
exit;
