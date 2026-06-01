<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

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

<style>
.filter-bar { margin-bottom: 1.5rem; }
.filter-bar__row { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
.filter-group { flex: 1; min-width: 160px; }
.filter-group .form-input { width: 100%; padding: 0.6rem 0.75rem; border-radius: var(--radius); border: 1px solid var(--border); background: var(--background); color: var(--foreground); font-size: 0.875rem; outline: none; }
.filter-group .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent); }

.table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: var(--radius); background: var(--background); }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: var(--muted-foreground); border-bottom: 1px solid var(--border); background: var(--secondary); white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); color: var(--foreground); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: color-mix(in srgb, var(--secondary) 40%, transparent); }
.empty-state { text-align: center; padding: 3rem !important; color: var(--muted-foreground); }
.product-name { color: var(--foreground); text-decoration: none; font-weight: 500; }
.product-name:hover { color: var(--primary); }
.price { font-weight: 600; white-space: nowrap; }
.barcode { font-family: monospace; font-size: 0.8rem; color: var(--muted-foreground); }
.actions { white-space: nowrap; }
.btn-icon { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: var(--radius); color: var(--muted-foreground); transition: all 0.15s; }
.btn-icon:hover { background: var(--secondary); color: var(--foreground); }
.btn-icon--danger:hover { background: #fef2f2; color: #dc2626; }
.badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 500; margin-left: 0.4rem; vertical-align: middle; }
.badge--warning { background: #fef3c7; color: #92400e; }
.badge--danger { background: #fef2f2; color: #991b1b; }
.stock-indicator { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-weight: 600; font-size: 0.8rem; background: #f0fdf4; color: #166534; }
.stock-indicator--low { background: #fef3c7; color: #92400e; }
.text-muted-foreground { color: var(--muted-foreground); }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
