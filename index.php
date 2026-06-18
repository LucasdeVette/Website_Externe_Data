<?php
require_once __DIR__ . '/includes/init.php';

use App\Service\AuthService;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;
use App\Repository\CategoryRepository;

// Handle login POST
$loginError = '';
if (!isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthService();
    if ($auth->login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /');
        exit;
    }
    $loginError = 'Ongeldige gebruikersnaam of wachtwoord.';
}

// Show login form if not authenticated
if (!isset($_SESSION['user_id'])):
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inloggen | <?= APP_NAME ?></title>
  <link rel="icon" type="image/svg+xml" href="/public/icon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css">
</head>
<body class="login-page">
  <div class="login-card">
    <div class="text-center" style="margin-bottom:2rem;">
      <a href="/" class="logo" style="justify-content:center;">
        <span class="logo-icon">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5h3l2.2 12.3a2 2 0 0 0 2 1.7h9.4a2 2 0 0 0 2-1.6L26 9H8.5" />
            <circle cx="13" cy="26" r="1.7" />
            <circle cx="23" cy="26" r="1.7" />
            <path d="M18.5 10.5l-3 4h3l-3 4" stroke="currentColor" stroke-width="1.7" />
          </svg>
        </span>
        <span class="logo-text"><?= APP_NAME ?></span>
      </a>
      <h1 style="font-size:1.5rem;font-weight:600;margin-top:1.5rem;">Welkom terug</h1>
      <p class="text-muted-foreground">Log in om door te gaan</p>
    </div>

    <?php if ($loginError): ?>
      <div class="login-error"><?= htmlspecialchars($loginError) ?></div>
    <?php endif; ?>

    <form method="POST" style="display:flex;flex-direction:column;gap:1rem;">
      <div class="field">
        <label for="username" class="field__label">Gebruikersnaam</label>
        <input id="username" name="username" type="text" class="field__input" placeholder="admin" required>
      </div>
      <div class="field">
        <label for="password" class="field__label">Wachtwoord</label>
        <input id="password" name="password" type="password" class="field__input" placeholder="wachtwoord" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Inloggen</button>
    </form>

    <p class="text-muted-foreground" style="font-size:0.85rem;margin-top:1.5rem;text-align:center;">
      Demo: <strong>admin</strong> / <strong>password</strong>
    </p>
    

    <p class="text-muted-foreground" style="font-size:0.85rem;margin-top:1rem;text-align:center;">
    Nog geen account?
    <a href="/register.php">Account registreren</a>
    </p>
    
  </div>
</body>
</html>
<?php
exit;
endif;

// Dashboard
$productRepo  = new ProductRepository();
$orderRepo    = new OrderRepository();
$categoryRepo = new CategoryRepository();

$totalProducts   = $productRepo->countTotal();
$lowStockCount   = $productRepo->countLowStock();
$outOfStock      = $productRepo->countOutOfStock();
$stockValue      = $productRepo->sumStockValue();
$totalOrders     = $orderRepo->countTotal();
$pendingOrders   = $orderRepo->countByStatus('pending');

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
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4 7.55 4.24a1 1 0 0 0-1.1 0L4 5.68"/><path d="M21 16a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h2"/><path d="m3.06 10.46 5.3 3.08a1 1 0 0 0 1.1 0l5.3-3.08"/><path d="M12 12.76V21"/><path d="M8 16v-2.46"/><path d="M16 13.54V16"/></svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $totalProducts ?></div>
        <div class="stat-card__label">Producten</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--orange">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $lowStockCount ?></div>
        <div class="stat-card__label">Bijna op voorraad</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--red">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $outOfStock ?></div>
        <div class="stat-card__label">Uit voorraad</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--green">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value">&euro;<?= number_format($stockValue, 0, ',', '.') ?></div>
        <div class="stat-card__label">Voorraadwaarde</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--blue">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17h14M5 17a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2M5 17l4-4 3 3 6-6"/><circle cx="5" cy="19" r="1"/><circle cx="19" cy="19" r="1"/></svg>
      </div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $totalOrders ?></div>
        <div class="stat-card__label">Bestellingen totaal</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon stat-card__icon--purple">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
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
          <thead><tr><th>Product</th><th>Categorie</th><th>Voorraad</th><th>Min.</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($lowStockProducts)): ?>
              <tr><td colspan="5" class="empty-state">Geen producten met lage voorraad</td></tr>
            <?php else: foreach ($lowStockProducts as $p): ?>
              <tr>
                <td><a href="/products/edit.php?id=<?= $p->getId() ?>" class="product-link"><?= htmlspecialchars($p->getName()) ?></a></td>
                <td class="text-muted-foreground"><?= htmlspecialchars($p->getCategory()?->getName() ?? '-') ?></td>
                <td><span class="stock-badge stock-badge--<?= $p->getStock() === 0 ? 'empty' : 'low' ?>"><?= $p->getStock() ?></span></td>
                <td><?= $p->getMinStock() ?></td>
                <td><span class="badge badge--<?= $p->getStock() === 0 ? 'danger' : 'warning' ?>"><?= $p->getStock() === 0 ? 'Uit voorraad' : 'Bijna op' ?></span></td>
              </tr>
            <?php endforeach; endif; ?>
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
          <thead><tr><th>#</th><th>Datum</th><th>Leverancier</th><th>Status</th><th>Totaal</th></tr></thead>
          <tbody>
            <?php if (empty($recentOrders)): ?>
              <tr><td colspan="5" class="empty-state">Geen bestellingen</td></tr>
            <?php else: foreach ($recentOrders as $o): ?>
              <tr>
                <td><a href="/orders/view.php?id=<?= $o->getId() ?>" class="product-link">#<?= $o->getId() ?></a></td>
                <td class="text-muted-foreground"><?= htmlspecialchars($o->getOrderDate()) ?></td>
                <td class="text-muted-foreground"><?= htmlspecialchars($o->getSupplier()?->getName() ?? '-') ?></td>
                <td><span class="badge badge--<?= match($o->getStatus()) { 'delivered' => 'success', 'cancelled' => 'danger', 'pending' => 'warning', default => 'info' } ?>"><?= $o->getStatusLabel() ?></span></td>
                <td>&euro;<?= number_format($o->getTotalAmount(), 2) ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="panel" style="margin-top:2rem;">
    <div class="panel__header"><h2 class="panel__title">Categorieën</h2></div>
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
