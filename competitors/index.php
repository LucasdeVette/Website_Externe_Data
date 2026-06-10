<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\CompetitorPriceRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Model\CompetitorPrice;
use App\Model\CompetitorStore;
use App\Service\PriceScraperService;

$repo        = new CompetitorPriceRepository();
$categoryRepo = new CategoryRepository();

$errors = [];
$edit   = null;

if (isset($_GET['edit'])) {
    $edit = $repo->findStoreById((int)$_GET['edit']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $action = $_POST['action'] ?? '';

    if (empty($errors) && in_array($action, ['create', 'update'])) {
        $name    = trim($_POST['name'] ?? '');
        $website = trim($_POST['website'] ?? '');

        if (empty($name)) $errors[] = 'Naam is verplicht.';

        if (empty($errors)) {
            if ($action === 'create') {
                $store = new CompetitorStore([
                    'name'    => $name,
                    'website' => $website ?: null,
                ]);
                $repo->createStore($store);
                flash('success', 'Concurrent "' . htmlspecialchars($name) . '" is toegevoegd.');
                header('Location: /competitors/index.php');
                exit;
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $s = $repo->findStoreById($id);
                if ($s) {
                    $s->setName($name);
                    $s->setWebsite($website ?: null);
                    $repo->updateStore($s);
                    flash('success', 'Concurrent "' . htmlspecialchars($name) . '" is bijgewerkt.');
                    header('Location: /competitors/index.php');
                    exit;
                }
            }
        }
    }

    if (empty($errors) && $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $s = $repo->findStoreById($id);
        if ($s) {
            $repo->deleteStore($id);
            flash('success', 'Concurrent "' . htmlspecialchars($s->getName()) . '" is verwijderd.');
        }
        header('Location: /competitors/index.php');
        exit;
    }

    if (empty($errors) && $action === 'fetch_all') {
        try {
            $prodRepo = new ProductRepository();
            $products = $prodRepo->findAll();
            $stores   = $repo->findStores();

            if (empty($stores)) {
                $errors[] = 'Voeg eerst concurrenten toe.';
            } else {
                $scraper  = new PriceScraperService();
                $results  = $scraper->fetchAllPrices($products, $stores);
                $saved = 0;

                $repo->getPdo()->exec('DELETE FROM competitor_prices');

                foreach ($results as $p) {
                    $cp = new CompetitorPrice([
                        'product_id'  => $p['product_id'],
                        'store_id'    => $p['store_id'],
                        'price'       => $p['price'],
                        'recorded_at' => date('Y-m-d'),
                    ]);
                    $repo->savePrice($cp);
                    $saved++;
                }

                flash('success', $saved . ' prijzen bijgewerkt via supermarkt-API.');
                header('Location: /competitors/index.php' . (isset($_GET['category_id']) ? '?category_id=' . (int)$_GET['category_id'] : ''));
                exit;
            }
        } catch (\Throwable $e) {
            $errors[] = 'Fout bij ophalen prijzen: ' . $e->getMessage();
        }
    }
}

$stores      = $repo->findStores();
$categories  = $categoryRepo->findAll();
$categoryId  = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;

try {
    $comparisons = $repo->getAllComparisons($categoryId);
} catch (\Throwable $e) {
    $errors[] = 'Fout bij ophalen vergelijkingen: ' . $e->getMessage();
    $comparisons = [];
}

$title = 'Concurrenten';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <h1 class="text-3xl font-semibold tracking-tight" style="margin-bottom:1.5rem;">Concurrenten</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert--error">
      <ul style="margin:0;padding-left:1.25rem;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid" style="grid-template-columns:1fr 1.5fr;gap:2rem;margin-bottom:3rem;">
    <div class="detail-card">
      <h3 class="detail-card__title"><?= $edit ? 'Concurrent bewerken' : 'Nieuwe concurrent' ?></h3>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit->getId() ?>">
        <?php endif; ?>

        <div class="form-stack" style="margin-bottom:1.25rem;">
          <div class="field">
            <label class="field__label" for="name">Naam *</label>
            <input id="name" name="name" type="text" class="field__input" value="<?= htmlspecialchars($_POST['name'] ?? ($edit ? $edit->getName() : '')) ?>" required>
          </div>
          <div class="field">
            <label class="field__label" for="website">Website</label>
            <input id="website" name="website" type="url" class="field__input" value="<?= htmlspecialchars($_POST['website'] ?? ($edit ? $edit->getWebsite() ?? '' : '')) ?>" placeholder="https://">
          </div>
        </div>

        <div class="flex gap-2">
          <button type="submit" class="btn btn-primary"><?= $edit ? 'Opslaan' : 'Toevoegen' ?></button>
          <?php if ($edit): ?>
            <a href="/competitors/index.php" class="btn btn-ghost">Annuleren</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Naam</th>
            <th>Website</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($stores)): ?>
            <tr><td colspan="3" class="empty-state">Nog geen concurrenten toegevoegd</td></tr>
          <?php else: ?>
            <?php foreach ($stores as $s): ?>
            <tr>
              <td class="font-medium"><?= htmlspecialchars($s->getName()) ?></td>
              <td class="text-sm"><?= $s->getWebsite() ? '<a href="' . htmlspecialchars($s->getWebsite()) . '" target="_blank" rel="noopener" class="product-link">' . htmlspecialchars($s->getWebsite()) . '</a>' : '-' ?></td>
              <td class="actions">
                <a href="/competitors/index.php?edit=<?= $s->getId() ?>" class="btn-icon" title="Bewerken">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Concurrent &#34;<?= htmlspecialchars($s->getName()) ?>&#34; verwijderen?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $s->getId() ?>">
                  <button type="submit" class="btn-icon btn-icon--danger" title="Verwijderen">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
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

  <hr style="border:none;border-top:1px solid var(--border);margin-bottom:2rem;">

  <div class="flex items-center justify-between" style="margin-bottom:2rem;">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight">Prijsvergelijking</h2>
      <p class="text-muted-foreground mt-1">Vergelijk productprijzen met concurrenten</p>
    </div>
    <div class="flex gap-2">
      <?php if (!empty($stores)): ?>
        <form method="POST" style="display:inline;">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="fetch_all">
          <button type="submit" class="btn btn-primary btn-sm">Update prijzen</button>
        </form>
      <?php endif; ?>
      <a href="/api/competitors.php<?= $categoryId ? '?category_id=' . $categoryId : '' ?>" target="_blank" class="btn btn-outline btn-sm">JSON API</a>
    </div>
  </div>

  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="/competitors/index.php" class="btn <?= !$categoryId ? 'btn-primary' : 'btn-ghost' ?> btn-sm">Alle</a>
    <?php foreach ($categories as $cat): ?>
      <a href="/competitors/index.php?category_id=<?= $cat->getId() ?>" class="btn <?= $categoryId === $cat->getId() ? 'btn-primary' : 'btn-ghost' ?> btn-sm">
        <?= htmlspecialchars($cat->getName()) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($stores)): ?>
    <div class="card-form" style="text-align:center;padding:2rem;">
      <p class="text-muted-foreground">Voeg eerst concurrenten toe om prijzen te vergelijken.</p>
    </div>
  <?php elseif (empty($comparisons)): ?>
    <div class="card-form" style="text-align:center;padding:2rem;">
      <p class="text-muted-foreground">Voeg eerst producten toe om prijzen te vergelijken.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Categorie</th>
            <th style="text-align:right;">Onze prijs</th>
            <th style="text-align:right;">Concurrent</th>
            <th style="text-align:right;">Hun prijs</th>
            <th style="text-align:right;">Verschil (&euro;)</th>
            <th>Vergelijking</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comparisons as $item):
            $first = true;
          ?>
            <?php foreach ($item['competitors'] as $c): ?>
              <tr>
                <?php if ($first): ?>
                  <td rowspan="<?= count($item['competitors']) ?>" style="vertical-align:top;">
                    <a href="/products/edit.php?id=<?= $item['product_id'] ?>" class="product-link"><?= htmlspecialchars($item['product_name']) ?></a>
                  </td>
                  <td rowspan="<?= count($item['competitors']) ?>" style="vertical-align:top;" class="text-muted-foreground"><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                  <td rowspan="<?= count($item['competitors']) ?>" style="vertical-align:top;text-align:right;font-weight:600;">
                    <a href="/products/edit.php?id=<?= $item['product_id'] ?>" class="product-link">&euro;<?= number_format($item['our_price'], 2) ?></a>
                  </td>
                <?php endif; ?>
                <td style="text-align:right;"><?= htmlspecialchars($c['store_name']) ?></td>

                <?php if ($c['price'] === null): ?>
                  <td colspan="3" class="text-muted-foreground" style="text-align:center;">
                    <a href="/competitors/edit-price.php?product_id=<?= $item['product_id'] ?>&store_id=<?= $c['store_id'] ?>" class="edit-link">Niet aanwezig</a>
                  </td>
                <?php else:
                  $diffEuro = round($c['price'] - $item['our_price'], 2);
                  $diffClass = $diffEuro < 0 ? 'diff--below' : ($diffEuro > 0 ? 'diff--above' : 'diff--equal');
                  $label = $diffEuro < 0
                    ? '&euro;' . number_format(abs($diffEuro), 2, ',', '') . ' <span class="diff--above">goedkoper bij ' . htmlspecialchars($c['store_name']) . '</span>'
                    : ($diffEuro > 0
                        ? '&euro;' . number_format($diffEuro, 2, ',', '') . ' <span class="diff--below">goedkoper bij ons</span>'
                        : '<span class="diff--equal">Zelfde prijs</span>');
                ?>
                  <td style="text-align:right;">
                    <a href="/competitors/edit-price.php?product_id=<?= $item['product_id'] ?>&store_id=<?= $c['store_id'] ?>" class="edit-link">&euro;<?= number_format($c['price'], 2) ?></a>
                  </td>
                  <td style="text-align:right;" class="<?= $diffClass ?> font-medium">
                    <?= $diffEuro > 0 ? '+' : '' ?>&euro;<?= number_format($diffEuro, 2, ',', '') ?>
                  </td>
                  <td><?= $label ?></td>
                <?php endif; ?>
              </tr>
              <?php $first = false; ?>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-sm text-muted-foreground" style="margin-top:0.75rem;">
      Bedragen in euro. Groen = onze prijs is lager (voordeel), rood = concurrent is goedkoper.
      Klik op een product of prijs om aan te passen.
    </p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
