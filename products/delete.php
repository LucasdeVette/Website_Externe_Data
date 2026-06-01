<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\ProductRepository;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /products/index.php');
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
    flash('error', 'Ongeldig token. Probeer opnieuw.');
    header('Location: /products/index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$repo = new ProductRepository();
$product = $repo->findById($id);
if (!$product) {
    flash('error', 'Product niet gevonden.');
    header('Location: /products/index.php');
    exit;
}

$repo->delete($id);
flash('success', 'Product "' . htmlspecialchars($product->getName()) . '" is verwijderd.');
header('Location: /products/index.php');
exit;
