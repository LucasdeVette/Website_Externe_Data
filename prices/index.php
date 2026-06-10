<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;

$productRepo  = new ProductRepository();
$categoryRepo = new CategoryRepository();

$categoryId   = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$categories   = $categoryRepo->findAll();
$comparison   = $productRepo->getPriceComparison($categoryId);

$title = 'Prijsvergelijking';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:2rem;">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Prijsvergelijking</h1>
      <p class="text-muted-foreground mt-1">Vergelijk productprijzen met categoriegemiddelden</p>
    </div>
    <a href="/api/prices.php" class="btn btn-outline" target="_blank">JSON API</a>
  </div>

  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="/prices/index.php" class="btn <?= !$categoryId ? 'btn-primary' : 'btn-ghost' ?>">Alle</a>
    <?php foreach ($categories as $cat): ?>
      <a href="/prices/index.php?category_id=<?= $cat->getId() ?>" class="btn <?= $categoryId === $cat->getId() ? 'btn-primary' : 'btn-ghost' ?>"><?= htmlspecialchars($cat->getName()) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($comparison)): ?>
    <div class="card-form" style="text-align:center;padding:3rem;">
      <p class="text-muted-foreground">Geen producten gevonden.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Categorie</th>
            <th>Leverancier</th>
            <th style="text-align:right;">Prijs</th>
            <th style="text-align:right;">Gemiddeld</th>
            <th style="text-align:right;">Min</th>
            <th style="text-align:right;">Max</th>
            <th style="text-align:right;">Verschil</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $prevCategory = '';
          foreach ($comparison as $item):
            $product = $item['product'];
            $diff = $item['diff_percent'];
            $diffClass = $diff < -5 ? 'diff--below' : ($diff > 5 ? 'diff--above' : 'diff--equal');
          ?>
          <tr>
            <td>
              <a href="/products/edit.php?id=<?= $product->getId() ?>" class="product-link"><?= htmlspecialchars($product->getName()) ?></a>
            </td>
            <td class="text-muted-foreground"><?= htmlspecialchars($product->getCategory()?->getName() ?? '-') ?></td>
            <td class="text-muted-foreground"><?= htmlspecialchars($product->getSupplier()?->getName() ?? '-') ?></td>
            <td style="text-align:right;font-weight:600;">&euro;<?= number_format($product->getPrice(), 2) ?></td>
            <td style="text-align:right;">&euro;<?= number_format($item['avg_price'], 2) ?></td>
            <td style="text-align:right;">&euro;<?= number_format($item['min_price'], 2) ?></td>
            <td style="text-align:right;">&euro;<?= number_format($item['max_price'], 2) ?></td>
            <td style="text-align:right;" class="<?= $diffClass ?>">
              <?= $diff > 0 ? '+' : '' ?><?= $diff ?>%
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <p class="text-sm text-muted-foreground" style="margin-top:0.75rem;">
      Getallen zijn afgerond. Verschuivingen getoond als percentage verschil t.o.v. categoriegemiddelde.
    </p>
  <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
