<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\ProductRepository;

$id = (int) ($_GET['id'] ?? 0);
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
