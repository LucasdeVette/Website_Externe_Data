<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\CompetitorPriceRepository;
use App\Repository\ProductRepository;
use App\Model\CompetitorPrice;

$compRepo = new CompetitorPriceRepository();
$prodRepo = new ProductRepository();

$productId = (int) ($_GET['product_id'] ?? 0);
$storeId   = (int) ($_GET['store_id'] ?? 0);

$product = $prodRepo->findById($productId);
$store   = $compRepo->findStoreById($storeId);

if (!$product || !$store) {
    flash('error', 'Product of concurrent niet gevonden.');
    header('Location: /competitors/index.php');
    exit;
}

$existing = $compRepo->findLatestPrice($productId, $storeId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $newPrice = trim($_POST['price'] ?? '');

    if ($newPrice === '') {
        $compRepo->getPdo()->prepare(
            'DELETE FROM competitor_prices WHERE product_id = ? AND store_id = ?'
        )->execute([$productId, $storeId]);
        flash('success', 'Prijs van "' . htmlspecialchars($store->getName()) . '" voor "' . htmlspecialchars($product->getName()) . '" is verwijderd.');
        header('Location: /competitors/index.php' . ($product->getCategoryId() ? '?category_id=' . $product->getCategoryId() : ''));
        exit;
    }

    if (!is_numeric($newPrice) || (float) $newPrice < 0) {
        $errors[] = 'Ongeldige prijs.';
    }

    if (empty($errors)) {
        $cp = new CompetitorPrice([
            'product_id'  => $productId,
            'store_id'    => $storeId,
            'price'       => (float) $newPrice,
            'recorded_at' => date('Y-m-d'),
        ]);
        $compRepo->savePrice($cp);
        flash('success', 'Prijs van "' . htmlspecialchars($store->getName()) . '" voor "' . htmlspecialchars($product->getName()) . '" is bijgewerkt naar &euro;' . number_format((float) $newPrice, 2, ',', '') . '.');
        header('Location: /competitors/index.php' . ($product->getCategoryId() ? '?category_id=' . $product->getCategoryId() : ''));
        exit;
    }
}

$title = 'Concurrentprijs aanpassen';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;">
    <a href="/competitors/index.php<?= $product->getCategoryId() ? '?category_id=' . $product->getCategoryId() : '' ?>" class="btn btn-ghost">&larr; Terug</a>
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Concurrentprijs aanpassen</h1>
      <p class="text-muted-foreground mt-1"><?= htmlspecialchars($product->getName()) ?> &mdash; <?= htmlspecialchars($store->getName()) ?></p>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="alert alert--error">
      <strong>Corrigeer de volgende fouten:</strong>
      <ul style="margin:0.5rem 0 0 1.25rem;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" class="form-card">
    <?= csrfField() ?>
    <div class="form-grid">
      <div class="field">
        <label class="field__label" for="product">Product</label>
        <input id="product" type="text" class="field__input" value="<?= htmlspecialchars($product->getName()) ?>" disabled>
      </div>
      <div class="field">
        <label class="field__label" for="store">Concurrent</label>
        <input id="store" type="text" class="field__input" value="<?= htmlspecialchars($store->getName()) ?>" disabled>
      </div>
      <div class="field">
        <label class="field__label" for="our_price">Onze prijs</label>
        <input id="our_price" type="text" class="field__input" value="&euro;<?= number_format($product->getPrice(), 2) ?>" disabled>
      </div>
      <div class="field">
        <label class="field__label" for="price">Hun prijs (&euro;)</label>
        <input id="price" name="price" type="number" step="0.01" min="0" class="field__input"
               value="<?= htmlspecialchars($_POST['price'] ?? ($existing ? $existing->getPrice() : '')) ?>"
               placeholder="Laat leeg om te verwijderen">
        <p class="field__help">Laat leeg om deze prijs te verwijderen (concurrent wordt gemarkeerd als "Niet aanwezig").</p>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Opslaan</button>
      <a href="/competitors/index.php<?= $product->getCategoryId() ? '?category_id=' . $product->getCategoryId() : '' ?>" class="btn btn-ghost">Annuleren</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
