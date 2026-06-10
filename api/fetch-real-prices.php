<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Service\PriceScraperService;
use App\Repository\CompetitorPriceRepository;
use App\Repository\ProductRepository;
use App\Model\CompetitorPrice;

header('Content-Type: application/json');

$scraper   = new PriceScraperService();
$compRepo  = new CompetitorPriceRepository();
$prodRepo  = new ProductRepository();

$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;
$storeId   = isset($_GET['store_id']) ? (int) $_GET['store_id'] : null;

if ($productId && $storeId) {
    $product = $prodRepo->findById($productId);
    $store   = $compRepo->findStoreById($storeId);

    if (!$product || !$store) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Product of winkel niet gevonden.']);
        exit;
    }

    $price = $scraper->fetchPrice($product->getName(), $product->getBarcode(), $store->getName());

    if ($price === null) {
        echo json_encode(['success' => false, 'error' => 'Geen prijs gevonden via externe API.']);
        exit;
    }

    $cp = new CompetitorPrice([
        'product_id'  => $product->getId(),
        'store_id'    => $store->getId(),
        'price'       => $price,
        'recorded_at' => date('Y-m-d'),
    ]);
    $compRepo->savePrice($cp);

    echo json_encode([
        'success' => true,
        'data'    => [
            'product'      => $product->getName(),
            'store'        => $store->getName(),
            'price'        => $price,
            'price_fmt'    => '€' . number_format($price, 2, ',', '.'),
            'recorded_at'  => date('Y-m-d'),
            'product_id'   => $product->getId(),
            'store_id'     => $store->getId(),
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_all'])) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Ongeldig token.']);
        exit;
    }

    $products = $prodRepo->findAll();
    $stores   = $compRepo->findStores();

    if (empty($stores)) {
        echo json_encode(['success' => false, 'error' => 'Voeg eerst concurrenten toe.']);
        exit;
    }

    $results = $scraper->fetchAllPrices($products, $stores);
    $saved = 0;

    $compRepo->getPdo()->exec('DELETE FROM competitor_prices');

    foreach ($results as $r) {
        $cp = new CompetitorPrice([
            'product_id'  => $r['product_id'],
            'store_id'    => $r['store_id'],
            'price'       => $r['price'],
            'recorded_at' => date('Y-m-d'),
        ]);
        $compRepo->savePrice($cp);
        $saved++;
    }

    echo json_encode([
        'success' => true,
        'data'    => [
            'products_scanned' => count($products),
            'prices_found'     => count($results),
            'prices_saved'     => $saved,
            'results'          => $results,
        ],
        'message' => count($results) . ' prijzen gevonden en opgeslagen van ' . count($products) . ' producten.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => false,
    'error'   => 'Geef product_id en store_id op, of POST met fetch_all=1.',
]);
