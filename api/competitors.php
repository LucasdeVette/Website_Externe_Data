<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\CompetitorPriceRepository;
use App\Repository\CategoryRepository;

header('Content-Type: application/json');

$compRepo     = new CompetitorPriceRepository();
$categoryRepo = new CategoryRepository();

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$format     = $_GET['format'] ?? 'full';

$comparisons = $compRepo->getAllComparisons($categoryId);
$stores      = $compRepo->findStores();

if ($format === 'simple') {
    $simple = [];
    foreach ($comparisons as $item) {
        $cheapest = null;
        $diffTotal = 0;
        foreach ($item['competitors'] as $c) {
            $diffTotal += $c['diff'];
            if (!$cheapest || $c['price'] < $cheapest['price']) {
                $cheapest = $c;
            }
        }
        $simple[] = [
            'product_id'   => $item['product_id'],
            'product_name' => $item['product_name'],
            'our_price'    => $item['our_price'],
            'cheapest_competitor' => $cheapest ? [
                'store_name' => $cheapest['store_name'],
                'price'      => $cheapest['price'],
                'diff'       => round($cheapest['price'] - $item['our_price'], 2),
            ] : null,
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
foreach ($comparisons as $item) {
    $competitors = [];
    foreach ($item['competitors'] as $c) {
        if ($c['price'] === null) {
            $competitors[] = [
                'store_id'   => $c['store_id'],
                'store_name' => $c['store_name'],
                'price'      => null,
                'diff'       => null,
                'diff_percent' => null,
                'label'      => 'niet aanwezig',
            ];
            continue;
        }
        $diffEuro = round($c['price'] - $item['our_price'], 2);
        $competitors[] = [
            'store_id'   => $c['store_id'],
            'store_name' => $c['store_name'],
            'price'      => $c['price'],
            'diff'       => $diffEuro,
            'diff_percent' => $c['diff'],
            'label'      => $diffEuro < 0
                ? '€' . number_format(abs($diffEuro), 2, ',', '') . ' goedkoper bij ' . $c['store_name']
                : ($diffEuro > 0
                    ? '€' . number_format($diffEuro, 2, ',', '') . ' goedkoper bij ons'
                    : 'zelfde prijs'),
        ];
    }
    $full[] = [
        'product_id'   => $item['product_id'],
        'product_name' => $item['product_name'],
        'category_id'  => $item['category_id'] ?? null,
        'category_name' => $item['category_name'],
        'our_price'    => $item['our_price'],
        'competitors'  => $competitors,
    ];
}

echo json_encode([
    'success' => true,
    'data'    => $full,
    'count'   => count($full),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
