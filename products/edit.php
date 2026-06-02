<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\SupplierRepository;

$productRepo  = new ProductRepository();
$categoryRepo = new CategoryRepository();
$supplierRepo = new SupplierRepository();

$id = (int) ($_GET['id'] ?? 0);
$product = $productRepo->findById($id);
if (!$product) {
    flash('error', 'Product niet gevonden.');
    header('Location: /products/index.php');
    exit;
}

$categories = $categoryRepo->findAll();
$suppliers  = $supplierRepo->findAll();
$errors     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }
    $name       = trim($_POST['name'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $supplierId = !empty($_POST['supplier_id']) ? (int) $_POST['supplier_id'] : null;
    $price      = trim($_POST['price'] ?? '');
    $stock      = trim($_POST['stock'] ?? '');

    if (empty($name)) $errors[] = 'Naam is verplicht.';
    if (!$categoryId) $errors[] = 'Categorie is verplicht.';
    if (!is_numeric($price) || (float)$price < 0) $errors[] = 'Ongeldige prijs.';
    if (!is_numeric($stock) || (int)$stock < 0) $errors[] = 'Ongeldige voorraad.';

    if (empty($errors)) {
        $product->setName($name);
        $product->setCategoryId($categoryId);
        $product->setSupplierId($supplierId);
        $product->setPrice((float) $price);
        $product->setStock((int) $stock);
        $product->setMinStock((int) ($_POST['min_stock'] ?: 10));
        $product->setDescription($_POST['description'] ?? '');
        $product->setBarcode($_POST['barcode'] ?: null);
        $productRepo->update($product);
        flash('success', 'Product "' . htmlspecialchars($product->getName()) . '" is bijgewerkt.');
        header('Location: /products/index.php');
        exit;
    }
}

$title = 'Product bewerken';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;">
    <a href="/products/index.php" class="btn btn-ghost">&larr; Terug</a>
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Product bewerken</h1>
      <p class="text-muted-foreground mt-1"><?= htmlspecialchars($product->getName()) ?></p>
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
        <label class="field__label" for="name">Naam *</label>
        <input id="name" name="name" type="text" class="field__input" value="<?= htmlspecialchars($_POST['name'] ?? $product->getName()) ?>" required>
      </div>
      <div class="field">
        <label class="field__label" for="category_id">Categorie *</label>
        <select id="category_id" name="category_id" class="field__input" required>
          <option value="">-- Selecteer --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat->getId() ?>" <?= ($_POST['category_id'] ?? $product->getCategoryId()) == $cat->getId() ? 'selected' : '' ?>><?= htmlspecialchars($cat->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="field__label" for="price">Prijs (&euro;) *</label>
        <input id="price" name="price" type="number" step="0.01" min="0" class="field__input" value="<?= htmlspecialchars($_POST['price'] ?? $product->getPrice()) ?>" required>
      </div>
      <div class="field">
        <label class="field__label" for="stock">Voorraad *</label>
        <input id="stock" name="stock" type="number" min="0" class="field__input" value="<?= htmlspecialchars($_POST['stock'] ?? $product->getStock()) ?>" required>
      </div>
      <div class="field">
        <label class="field__label" for="min_stock">Min. voorraad</label>
        <input id="min_stock" name="min_stock" type="number" min="0" class="field__input" value="<?= htmlspecialchars($_POST['min_stock'] ?? $product->getMinStock()) ?>">
      </div>
      <div class="field">
        <label class="field__label" for="supplier_id">Leverancier</label>
        <select id="supplier_id" name="supplier_id" class="field__input">
          <option value="">-- Geen --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s->getId() ?>" <?= ($_POST['supplier_id'] ?? $product->getSupplierId()) == $s->getId() ? 'selected' : '' ?>><?= htmlspecialchars($s->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="grid-column:1/-1;">
        <label class="field__label" for="barcode">Barcode</label>
        <input id="barcode" name="barcode" type="text" class="field__input" value="<?= htmlspecialchars($_POST['barcode'] ?? $product->getBarcode() ?? '') ?>">
      </div>
      <div class="field" style="grid-column:1/-1;">
        <label class="field__label" for="description">Beschrijving</label>
        <textarea id="description" name="description" class="field__input" rows="3"><?= htmlspecialchars($_POST['description'] ?? $product->getDescription() ?? '') ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Opslaan
      </button>
      <form method="POST" action="/products/delete.php" style="display:inline;" onsubmit="return confirm('Product \'<?= htmlspecialchars($product->getName()) ?>\' verwijderen?');">
        <?= csrfField() ?>
        <input type="hidden" name="id" value="<?= $product->getId() ?>">
        <button type="submit" class="btn btn-outline" style="color:var(--destructive);border-color:color-mix(in srgb, var(--destructive) 30%, transparent);">Verwijderen</button>
      </form>
      <a href="/products/index.php" class="btn btn-ghost">Annuleren</a>
    </div>
  </form>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
