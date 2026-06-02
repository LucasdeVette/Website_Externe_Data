<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\OrderRepository;

$id = (int) ($_GET['id'] ?? 0);
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
