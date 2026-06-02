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

  <div class="stat-cards-grid">
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


<?php require_once __DIR__ . '/includes/footer.php'; ?>
