<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Service\ApiService;
use App\Repository\ProductRepository;
use App\Model\Product;

$apiService = new ApiService();
$productRepo = new ProductRepository();

$searchResults = [];
$apiQuery = $_GET['q'] ?? '';

if ($apiQuery) {
    $searchResults = $apiService->searchProducts($apiQuery);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        flash('error', 'Ongeldig token. Probeer opnieuw.');
        header('Location: /api/index.php');
        exit;
    }

    $productData = [
        'name' => $_POST['name'] ?? '',
        'barcode' => $_POST['barcode'] ?? null,
        'description' => $_POST['description'] ?? null,
        'price' => (float) ($_POST['price'] ?? 0),
        'image_url' => $_POST['image_url'] ?? null,
        'category_id' => 1,
        'stock' => 0,
        'min_stock' => 10,
    ];

    $existing = $productRepo->findAll($productData['barcode']);
    $exists = false;
    foreach ($existing as $p) {
        if ($p->getBarcode() === $productData['barcode']) {
            $exists = true;
            break;
        }
    }

    if (!$exists && !empty($productData['name'])) {
        $product = new Product($productData);
        $productRepo->create($product);
        flash('success', 'Product "' . htmlspecialchars($productData['name']) . '" is geïmporteerd via Open Food Facts.');
        header('Location: /api/index.php');
        exit;
    }
}

$title = 'API - Externe data';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <h1 class="text-3xl font-semibold tracking-tight" style="margin-bottom:0.5rem;">Externe API</h1>
  <p class="text-muted-foreground" style="margin-bottom:2rem;">
    Zoek producten via <strong>Open Food Facts</strong> en importeer ze in de database.
  </p>

  <form method="GET" class="flex items-center gap-3" style="margin-bottom:2rem;">
    <input type="search" name="q" class="form-input" style="flex:1;min-width:200px;max-width:400px;" placeholder="Zoek product (bv. 'chocolate', 'pasta')..." value="<?= htmlspecialchars($apiQuery) ?>" required>
    <button type="submit" class="btn btn-primary">Zoeken</button>
  </form>

  <?php if ($apiQuery && !empty($searchResults['products'])): ?>
    <p class="text-sm text-muted-foreground" style="margin-bottom:1.5rem;">
      <?= $searchResults['total'] ?> resultaten gevonden voor "<?= htmlspecialchars($apiQuery) ?>"
    </p>

    <div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:1rem;">
      <?php foreach ($searchResults['products'] as $item): ?>
      <div class="api-card">
        <?php if ($item['image_url']): ?>
          <div class="api-card__img">
            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" onerror="this.style.display='none'">
          </div>
        <?php endif; ?>
        <div class="api-card__body">
          <h3 class="api-card__title"><?= htmlspecialchars($item['name']) ?></h3>
          <?php if ($item['brand']): ?>
            <p class="api-card__brand"><?= htmlspecialchars($item['brand']) ?></p>
          <?php endif; ?>
          <?php if ($item['barcode']): ?>
            <p class="api-card__barcode">Barcode: <?= htmlspecialchars($item['barcode']) ?></p>
          <?php endif; ?>
          <?php if ($item['quantity']): ?>
            <p class="api-card__qty">Verpakking: <?= htmlspecialchars($item['quantity']) ?></p>
          <?php endif; ?>

          <form method="POST" style="margin-top:0.75rem;">
            <?= csrfField() ?>
            <input type="hidden" name="import" value="1">
            <input type="hidden" name="name" value="<?= htmlspecialchars($item['name']) ?>">
            <input type="hidden" name="barcode" value="<?= htmlspecialchars($item['barcode'] ?? '') ?>">
            <input type="hidden" name="description" value="<?= htmlspecialchars($item['description'] ?? '') ?>">
            <input type="hidden" name="price" value="<?= $item['price'] ?>">
            <input type="hidden" name="image_url" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
              Importeren in database
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php elseif ($apiQuery): ?>
    <div class="card-form" style="text-align:center;padding:3rem;">
      <p class="text-muted-foreground">Geen resultaten gevonden voor "<?= htmlspecialchars($apiQuery) ?>".</p>
    </div>
  <?php else: ?>
    <div class="card-form" style="text-align:center;padding:3rem;">
      <div style="font-size:3rem;margin-bottom:1rem;opacity:0.3;">
        <svg width="64" height="64" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;">
          <path d="M4 5h3l2.2 12.3a2 2 0 0 0 2 1.7h9.4a2 2 0 0 0 2-1.6L26 9H8.5" />
          <circle cx="13" cy="26" r="1.7" />
          <circle cx="23" cy="26" r="1.7" />
          <path d="M18.5 10.5l-3 4h3l-3 4" stroke="currentColor" stroke-width="1.7" />
        </svg>
      </div>
      <p class="text-muted-foreground">Zoek naar producten via de Open Food Facts API en importeer ze in je database.</p>
      <p class="text-sm text-muted-foreground" style="margin-top:0.5rem;">Bijv. <a href="?q=chocolate" style="color:var(--primary);">chocolade</a>, <a href="?q=pasta" style="color:var(--primary);">pasta</a>, <a href="?q=rijst" style="color:var(--primary);">rijst</a></p>
    </div>
  <?php endif; ?>
</div>

<style>
.api-card { background: var(--background); border: 1px solid var(--border); border-radius: calc(var(--radius) + 0.25rem); overflow: hidden; display: flex; flex-direction: column; }
.api-card__img { height: 140px; background: var(--secondary); display: flex; align-items: center; justify-content: center; padding: 1rem; }
.api-card__img img { max-height: 100%; max-width: 100%; object-fit: contain; }
.api-card__body { padding: 1rem; flex: 1; display: flex; flex-direction: column; }
.api-card__title { font-size: 0.95rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--foreground); }
.api-card__brand { font-size: 0.8rem; color: var(--muted-foreground); margin-bottom: 0.25rem; }
.api-card__barcode, .api-card__qty { font-size: 0.75rem; color: var(--muted-foreground); font-family: monospace; }
.card-form { background: var(--background); border: 1px solid var(--border); border-radius: calc(var(--radius) + 0.25rem); padding: 2rem; }
.form-input { padding: 0.6rem 0.75rem; border-radius: var(--radius); border: 1px solid var(--border); background: var(--background); color: var(--foreground); font-size: 0.875rem; outline: none; }
.form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent); }
.msg { padding: 0.75rem 1rem; border-radius: var(--radius); margin-bottom: 1.5rem; font-size: 0.875rem; }
.msg--success { background: color-mix(in srgb, #22c55e 15%, transparent); border: 1px solid color-mix(in srgb, #22c55e 30%, transparent); color: #166534; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
