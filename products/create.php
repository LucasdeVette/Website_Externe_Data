<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\SupplierRepository;
use App\Model\Product;

$productRepo  = new ProductRepository();
$categoryRepo = new CategoryRepository();
$supplierRepo = new SupplierRepository();
$categories   = $categoryRepo->findAll();
$suppliers    = $supplierRepo->findAll();

$errors = [];
$data = [
    'category_id' => $_POST['category_id'] ?? '',
    'supplier_id' => $_POST['supplier_id'] ?? '',
    'name'        => $_POST['name'] ?? '',
    'description' => $_POST['description'] ?? '',
    'price'       => $_POST['price'] ?? '',
    'stock'       => $_POST['stock'] ?? '',
    'min_stock'   => $_POST['min_stock'] ?? '10',
    'barcode'     => $_POST['barcode'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }
    $data['name'] = trim($data['name'] ?? '');
    $data['price'] = trim($data['price'] ?? '');
    $data['stock'] = trim($data['stock'] ?? '');
    if (empty($data['name'])) $errors[] = 'Naam is verplicht.';
    if (empty($data['category_id'])) $errors[] = 'Categorie is verplicht.';
    if (!is_numeric($data['price']) || (float)$data['price'] < 0) $errors[] = 'Voer een geldige prijs in.';
    if (!is_numeric($data['stock']) || (int)$data['stock'] < 0) $errors[] = 'Voer een geldige voorraad in.';

    if (empty($errors)) {
        $product = new Product([
            'category_id' => (int) $data['category_id'],
            'supplier_id' => !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
            'name'        => trim($data['name']),
            'description' => $data['description'] ?: null,
            'price'       => (float) $data['price'],
            'stock'       => (int) $data['stock'],
            'min_stock'   => (int) ($data['min_stock'] ?: 10),
            'barcode'     => $data['barcode'] ?: null,
        ]);
        $productRepo->create($product);
        flash('success', 'Product "' . htmlspecialchars($product->getName()) . '" is toegevoegd.');
        header('Location: /products/index.php');
        exit;
    }
}

$title = 'Product toevoegen';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;">
    <a href="/products/index.php" class="btn btn-ghost">&larr; Terug</a>
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Product toevoegen</h1>
      <p class="text-muted-foreground mt-1">Voeg een nieuw product toe aan de database</p>
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
        <input id="name" name="name" type="text" class="field__input" value="<?= htmlspecialchars($data['name']) ?>" placeholder="Bijv. Volle Melk 1L" required>
      </div>
      <div class="field">
        <label class="field__label" for="category_id">Categorie *</label>
        <select id="category_id" name="category_id" class="field__input" required>
          <option value="">-- Selecteer --</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat->getId() ?>" <?= $data['category_id'] == $cat->getId() ? 'selected' : '' ?>><?= htmlspecialchars($cat->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="field__label" for="price">Prijs (&euro;) *</label>
        <input id="price" name="price" type="number" step="0.01" min="0" class="field__input" value="<?= htmlspecialchars($data['price']) ?>" placeholder="0.00" required>
      </div>
      <div class="field">
        <label class="field__label" for="stock">Voorraad *</label>
        <input id="stock" name="stock" type="number" min="0" class="field__input" value="<?= htmlspecialchars($data['stock']) ?>" placeholder="0" required>
      </div>
      <div class="field">
        <label class="field__label" for="min_stock">Minimale voorraad</label>
        <input id="min_stock" name="min_stock" type="number" min="0" class="field__input" value="<?= htmlspecialchars($data['min_stock']) ?>" placeholder="10">
      </div>
      <div class="field">
        <label class="field__label" for="supplier_id">Leverancier</label>
        <select id="supplier_id" name="supplier_id" class="field__input">
          <option value="">-- Geen --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s->getId() ?>" <?= $data['supplier_id'] == $s->getId() ? 'selected' : '' ?>><?= htmlspecialchars($s->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field" style="grid-column:1/-1;">
        <label class="field__label" for="barcode">Barcode</label>
        <input id="barcode" name="barcode" type="text" class="field__input" value="<?= htmlspecialchars($data['barcode']) ?>" placeholder="8712345678901">
      </div>
      <div class="field" style="grid-column:1/-1;">
        <label class="field__label" for="description">Beschrijving</label>
        <textarea id="description" name="description" class="field__input" rows="3" placeholder="Optionele beschrijving van het product..."><?= htmlspecialchars($data['description']) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Opslaan
      </button>
      <a href="/products/index.php" class="btn btn-ghost">Annuleren</a>
    </div>
  </form>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
