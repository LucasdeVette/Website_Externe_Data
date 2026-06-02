<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

header('Content-Type: application/json');

$productRepo  = new ProductRepository();
$categoryRepo = new CategoryRepository();

$categoryId   = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$format       = $_GET['format'] ?? 'full';

$comparison = $productRepo->getPriceComparison($categoryId);

if ($format === 'simple') {
    $simple = [];
    foreach ($comparison as $item) {
        $p = $item['product'];
        $simple[] = [
            'id'           => $p->getId(),
            'name'         => $p->getName(),
            'price'        => $p->getPrice(),
            'category'     => $p->getCategory()?->getName(),
            'supplier'     => $p->getSupplier()?->getName(),
            'avg_price'    => $item['avg_price'],
            'min_price'    => $item['min_price'],
            'max_price'    => $item['max_price'],
            'diff_percent' => $item['diff_percent'],
        ];
    }
    echo json_encode([
        'success' => true,
        'data'    => $simple,
        'count'   => count($simple),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$full = [];
foreach ($comparison as $item) {
    $p = $item['product'];
    $full[] = [
        'product' => [
            'id'          => $p->getId(),
            'name'        => $p->getName(),
            'description' => $p->getDescription(),
            'price'       => $p->getPrice(),
            'stock'       => $p->getStock(),
            'barcode'     => $p->getBarcode(),
            'category'    => $p->getCategory() ? [
                'id'   => $p->getCategory()->getId(),
                'name' => $p->getCategory()->getName(),
            ] : null,
            'supplier'    => $p->getSupplier() ? [
                'id'   => $p->getSupplier()->getId(),
                'name' => $p->getSupplier()->getName(),
            ] : null,
        ],
        'statistics' => [
            'category_avg' => $item['avg_price'],
            'category_min' => $item['min_price'],
            'category_max' => $item['max_price'],
            'diff_percent' => $item['diff_percent'],
        ],
    ];
}

echo json_encode([
    'success' => true,
    'filters' => [
        'category_id' => $categoryId,
    ],
    'data'  => $full,
    'count' => count($full),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
