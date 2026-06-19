<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\ProductRepository;
use App\Service\ApiService;

header('Content-Type: application/json');

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$format = $_GET['format'] ?? 'full';
$comparison = (new ProductRepository())->getPriceComparison($categoryId);
$marktPrices = (new ApiService())->fetchAllMarketPrices($comparison);

if ($format === 'simple') {
    $simple = [];
    foreach ($comparison as $item) {
        $p = $item['product'];
        $simple[] = [
            'id' => $p->getId(), 'name' => $p->getName(),
            'price' => $p->getPrice(), 'api_price' => $marktPrices[$p->getId()] ?? null,
            'category' => $p->getCategory()?->getName(), 'supplier' => $p->getSupplier()?->getName(),
        ];
    }
    echo json_encode(['success' => true, 'data' => $simple, 'count' => count($simple)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$full = [];
foreach ($comparison as $item) {
    $p = $item['product'];
    $full[] = [
        'product' => [
            'id' => $p->getId(), 'name' => $p->getName(), 'description' => $p->getDescription(),
            'price' => $p->getPrice(), 'api_price' => $marktPrices[$p->getId()] ?? null,
            'stock' => $p->getStock(), 'barcode' => $p->getBarcode(),
            'category' => $p->getCategory() ? ['id' => $p->getCategory()->getId(), 'name' => $p->getCategory()->getName()] : null,
            'supplier' => $p->getSupplier() ? ['id' => $p->getSupplier()->getId(), 'name' => $p->getSupplier()->getName()] : null,
        ],
    ];
}

echo json_encode(['success' => true, 'filters' => ['category_id' => $categoryId], 'data' => $full, 'count' => count($full)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
