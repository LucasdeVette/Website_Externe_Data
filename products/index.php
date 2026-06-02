<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

$productRepo  = new ProductRepository();
$categoryRepo = new CategoryRepository();

$search     = $_GET['search'] ?? null;
$sort       = $_GET['sort'] ?? null;
$categoryId = $_GET['category_id'] ?? null;

$products   = $productRepo->findAll($search, $sort, $categoryId ? (int)$categoryId : null);
$categories = $categoryRepo->findAll();

$title = 'Producten';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:1.5rem;">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Producten</h1>
      <p class="text-muted-foreground mt-1"><?= count($products) ?> product<?= count($products) !== 1 ? 'en' : '' ?> gevonden</p>
    </div>
    <a href="/products/create.php" class="btn btn-primary">+ Nieuw product</a>
  </div>

  <form method="GET" class="filter-bar">
    <div class="filter-bar__row">
      <div class="filter-group">
        <input type="search" name="search" class="form-input" placeholder="Zoeken op naam, barcode..." value="<?= htmlspecialchars($search ?? '') ?>">
      </div>
      <div class="filter-group">
        <select name="category_id" class="form-input">
          <option value="">Alle categorieën</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat->getId() ?>" <?= $categoryId == $cat->getId() ? 'selected' : '' ?>><?= htmlspecialchars($cat->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-group">
        <select name="sort" class="form-input">
          <option value="">Sorteer: naam</option>
          <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prijs oplopend</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prijs aflopend</option>
          <option value="stock_asc" <?= $sort === 'stock_asc' ? 'selected' : '' ?>>Voorraad laag &rarr; hoog</option>
          <option value="stock_desc" <?= $sort === 'stock_desc' ? 'selected' : '' ?>>Voorraad hoog &rarr; laag</option>
          <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Naam Z &rarr; A</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filteren</button>
      <a href="/products/index.php" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Naam</th>
          <th>Categorie</th>
          <th>Leverancier</th>
          <th>Prijs</th>
          <th>Voorraad</th>
          <th>Barcode</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
          <tr><td colspan="7" class="empty-state">Geen producten gevonden</td></tr>
        <?php else: ?>
          <?php foreach ($products as $p): ?>
          <tr>
            <td>
              <a href="/products/edit.php?id=<?= $p->getId() ?>" class="product-name">
                <?= htmlspecialchars($p->getName()) ?>
              </a>
              <?php if ($p->isLowStock()): ?>
                <span class="badge badge--<?= $p->getStock() === 0 ? 'danger' : 'warning' ?>">
                  <?= $p->getStock() === 0 ? 'Uit voorraad' : 'Bijna op' ?>
                </span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($p->getCategory()?->getName() ?? '-') ?></td>
            <td class="text-muted-foreground"><?= htmlspecialchars($p->getSupplier()?->getName() ?? '-') ?></td>
            <td class="price">&euro;<?= number_format($p->getPrice(), 2) ?></td>
            <td>
              <span class="stock-indicator <?= $p->isLowStock() ? 'stock-indicator--low' : '' ?>">
                <?= $p->getStock() ?>
              </span>
            </td>
            <td class="barcode"><?= htmlspecialchars($p->getBarcode() ?? '-') ?></td>
            <td class="actions">
              <a href="/products/edit.php?id=<?= $p->getId() ?>" class="btn-icon" title="Bewerken">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </a>
              <form method="POST" action="/products/delete.php" style="display:inline;" onsubmit="return confirm('Product verwijderen?');">
                <?= csrfField() ?>
                <input type="hidden" name="id" value="<?= $p->getId() ?>">
                <button type="submit" class="btn-icon btn-icon--danger" title="Verwijderen">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
