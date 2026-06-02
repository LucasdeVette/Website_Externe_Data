<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/auth.php';

use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\CategoryRepository;

$productRepo  = new ProductRepository();
$orderRepo    = new OrderRepository();
$categoryRepo = new CategoryRepository();

$totalProducts  = $productRepo->countTotal();
$lowStockCount  = $productRepo->countLowStock();
$outOfStock     = $productRepo->countOutOfStock();
$stockValue     = $productRepo->sumStockValue();
$totalOrders    = $orderRepo->countTotal();
$pendingOrders  = $orderRepo->countByStatus('pending');
$deliveredOrders = $orderRepo->countByStatus('delivered');

$lowStockProducts = $productRepo->getLowStock(5);
$recentOrders     = $orderRepo->findRecent(5);
$categories       = $categoryRepo->findAll();

$title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:2rem;">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Dashboard</h1>
      <p class="text-muted-foreground mt-1">Overzicht van alle bedrijfsgegevens</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="/products/create.php" class="btn btn-primary">+ Nieuw product</a>
      <a href="/orders/create.php" class="btn btn-outline">+ Nieuwe bestelling</a>
    </div>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16.5 9.4 7.55 4.24a1 1 0 0 0-1.1 0L4 5.68"/><path d="M21 16a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/><path d="m3.06 10.46 5.3 3.08a1 1 0 0 0 1.1 0l5.3-3.08"/><path d="M12 12.76V21"/><path d="M8 16v-2.46"/><path d="M16 13.54V16"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $totalProducts ?></div>
        <div class="stat-card__label">Producten</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--orange">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $lowStockCount ?></div>
        <div class="stat-card__label">Bijna op voorraad</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--red">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $outOfStock ?></div>
        <div class="stat-card__label">Uit voorraad</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--green">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value">&euro;<?= number_format($stockValue, 0, ',', '.') ?></div>
        <div class="stat-card__label">Voorraadwaarde</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 17h14M5 17a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2M5 17l4-4 3 3 6-6"/><circle cx="5" cy="19" r="1"/><circle cx="19" cy="19" r="1"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $totalOrders ?></div>
        <div class="stat-card__label">Bestellingen totaal</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--purple">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $pendingOrders ?></div>
        <div class="stat-card__label">Openstaand</div>
      </div>
    </div>
  </div>

  <div class="dashboard-panels">
    <section class="panel">
      <div class="panel__header">
        <h2 class="panel__title">Bijna uit voorraad</h2>
        <a href="/products/index.php?sort=stock_asc" class="panel__link">Alle producten &rarr;</a>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Product</th><th>Categorie</th><th>Voorraad</th><th>Min.</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php if (empty($lowStockProducts)): ?>
              <tr><td colspan="5" class="empty-state">Geen producten met lage voorraad</td></tr>
            <?php else: ?>
              <?php foreach ($lowStockProducts as $p): ?>
              <tr>
                <td>
                  <a href="/products/edit.php?id=<?= $p->getId() ?>" class="product-link"><?= htmlspecialchars($p->getName()) ?></a>
                </td>
                <td class="text-muted-foreground"><?= htmlspecialchars($p->getCategory()?->getName() ?? '-') ?></td>
                <td><span class="stock-badge stock-badge--<?= $p->getStock() === 0 ? 'empty' : 'low' ?>"><?= $p->getStock() ?></span></td>
                <td><?= $p->getMinStock() ?></td>
                <td>
                  <?php if ($p->getStock() === 0): ?>
                    <span class="badge badge--danger">Uit voorraad</span>
                  <?php else: ?>
                    <span class="badge badge--warning">Bijna op</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel">
      <div class="panel__header">
        <h2 class="panel__title">Recente bestellingen</h2>
        <a href="/orders/index.php" class="panel__link">Alle bestellingen &rarr;</a>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>#</th><th>Datum</th><th>Leverancier</th><th>Status</th><th>Totaal</th></tr>
          </thead>
          <tbody>
            <?php if (empty($recentOrders)): ?>
              <tr><td colspan="5" class="empty-state">Geen bestellingen</td></tr>
            <?php else: ?>
              <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td><a href="/orders/view.php?id=<?= $o->getId() ?>" class="product-link">#<?= $o->getId() ?></a></td>
                <td class="text-muted-foreground"><?= htmlspecialchars($o->getOrderDate()) ?></td>
                <td class="text-muted-foreground"><?= htmlspecialchars($o->getSupplier()?->getName() ?? '-') ?></td>
                <td>
                  <span class="badge badge--<?= match($o->getStatus()) { 'delivered' => 'success', 'cancelled' => 'danger', 'pending' => 'warning', default => 'info' } ?>">
                    <?= $o->getStatusLabel() ?>
                  </span>
                </td>
                <td>&euro;<?= number_format($o->getTotalAmount(), 2) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="panel" style="margin-top:2rem;">
    <div class="panel__header">
      <h2 class="panel__title">Categorieën</h2>
    </div>
    <div class="categories-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="/products/index.php?category_id=<?= $cat->getId() ?>" class="category-card">
        <h3 class="category-card__name"><?= htmlspecialchars($cat->getName()) ?></h3>
        <p class="category-card__desc"><?= htmlspecialchars($cat->getDescription() ?? '') ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<style>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: var(--background);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.stat-card__icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-card__icon--blue { background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary); }
.stat-card__icon--orange { background: #fef3c7; color: #d97706; }
.stat-card__icon--red { background: #fef2f2; color: #dc2626; }
.stat-card__icon--green { background: #f0fdf4; color: #16a34a; }
.stat-card__icon--purple { background: #f3e8ff; color: #7c3aed; }
.stat-card__value { font-size: 1.5rem; font-weight: 700; font-family: var(--font-heading); line-height: 1.2; }
.stat-card__label { font-size: 0.8rem; color: var(--muted-foreground); margin-top: 0.1rem; }

.dashboard-panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 0;
}
@media (max-width: 768px) {
  .dashboard-panels { grid-template-columns: 1fr; }
}
.panel {
  background: var(--background);
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 0.25rem);
  overflow: hidden;
}
.panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}
.panel__title { font-size: 1rem; font-weight: 600; }
.panel__link { font-size: 0.8rem; color: var(--primary); text-decoration: none; }
.panel__link:hover { text-decoration: underline; }

.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { text-align: left; padding: 0.7rem 1.25rem; font-weight: 600; color: var(--muted-foreground); border-bottom: 1px solid var(--border); background: var(--secondary); white-space: nowrap; }
.data-table td { padding: 0.7rem 1.25rem; border-bottom: 1px solid var(--border); color: var(--foreground); }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: color-mix(in srgb, var(--secondary) 40%, transparent); }
.empty-state { text-align: center; padding: 2.5rem !important; color: var(--muted-foreground); }
.product-link { color: var(--foreground); text-decoration: none; font-weight: 500; }
.product-link:hover { color: var(--primary); }

.stock-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 9999px; font-weight: 600; font-size: 0.8rem; }
.stock-badge--low { background: #fef3c7; color: #92400e; }
.stock-badge--empty { background: #fef2f2; color: #991b1b; }

.badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 500; white-space: nowrap; }
.badge--success { background: #f0fdf4; color: #166534; }
.badge--warning { background: #fef3c7; color: #92400e; }
.badge--danger { background: #fef2f2; color: #991b1b; }
.badge--info { background: #eff6ff; color: #1e40af; }

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 0.75rem;
  padding: 1.25rem;
}
.category-card {
  display: block;
  padding: 1rem;
  background: var(--secondary);
  border-radius: var(--radius);
  text-decoration: none;
  transition: all 0.15s;
}
.category-card:hover { background: var(--primary); }
.category-card:hover .category-card__name,
.category-card:hover .category-card__desc { color: white; }
.category-card__name { font-size: 0.9rem; font-weight: 600; color: var(--foreground); margin-bottom: 0.15rem; }
.category-card__desc { font-size: 0.75rem; color: var(--muted-foreground); }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
