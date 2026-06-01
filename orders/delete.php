<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\OrderRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /orders/index.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    flash('error', 'Ongeldig token. Probeer opnieuw.');
    header('Location: /orders/index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$repo = new OrderRepository();
$order = $repo->findById($id);
if (!$order) {
    flash('error', 'Bestelling niet gevonden.');
    header('Location: /orders/index.php');
    exit;
}

$repo->delete($id);
flash('success', 'Bestelling #' . $id . ' is verwijderd.');
header('Location: /orders/index.php');
exit;
