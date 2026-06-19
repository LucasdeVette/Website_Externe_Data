<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Service\ApiService;

$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$comparison = (new ProductRepository())->getPriceComparison($categoryId);
$marktPrices = (new ApiService())->fetchAllMarketPrices($comparison);
$title = 'Prijsvergelijking';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:2rem;">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Prijsvergelijking</h1>
      <p class="text-muted-foreground mt-1">Vergelijk productprijzen met actuele marktprijzen</p>
    </div>
    <a href="/api/prices.php" class="btn btn-outline" target="_blank">JSON API</a>
  </div>
  <div class="flex items-center gap-3" style="margin-bottom:1.5rem;flex-wrap:wrap;">
    <a href="/prices/index.php" class="btn <?= !$categoryId ? 'btn-primary' : 'btn-ghost' ?>">Alle</a>
    <?php foreach ((new CategoryRepository())->findAll() as $cat): ?>
      <a href="/prices/index.php?category_id=<?= $cat->getId() ?>" class="btn <?= $categoryId === $cat->getId() ? 'btn-primary' : 'btn-ghost' ?>"><?= htmlspecialchars($cat->getName()) ?></a>
    <?php endforeach; ?>
  </div>
  <?php if (empty($comparison)): ?>
    <div class="card-form" style="text-align:center;padding:3rem;"><p class="text-muted-foreground">Geen producten gevonden.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Product</th><th>Categorie</th><th>Leverancier</th>
            <th style="text-align:right;">Prijs (ingevoerd)</th>
            <th style="text-align:right;">Marktprijs</th>
            <th style="text-align:right;">Verschil</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comparison as $item):
            $p = $item['product'];
            $dbPrice = $p->getPrice();
            $mp = $marktPrices[$p->getId()] ?? null;
            $diff = $dbPrice > 0 ? round(((($mp ?? $dbPrice) - $dbPrice) / $dbPrice) * 100, 1) : 0;
            $dc = $diff < -5 ? 'diff--below' : ($diff > 5 ? 'diff--above' : 'diff--equal');
          ?>
          <tr>
            <td><a href="/products/edit.php?id=<?= $p->getId() ?>" class="product-link"><?= htmlspecialchars($p->getName()) ?></a></td>
            <td class="text-muted-foreground"><?= htmlspecialchars($p->getCategory()?->getName() ?? '-') ?></td>
            <td class="text-muted-foreground"><?= htmlspecialchars($p->getSupplier()?->getName() ?? '-') ?></td>
            <td style="text-align:right;">&euro;<?= number_format($dbPrice, 2) ?></td>
            <td style="text-align:right;font-weight:600;">
              <?php if ($mp !== null): ?>&euro;<?= number_format($mp, 2) ?>
              <?php else: ?><span class="text-muted-foreground">&euro;<?= number_format($dbPrice, 2) ?></span><?php endif; ?>
            </td>
            <td style="text-align:right;" class="<?= $dc ?>"><?= $diff > 0 ? '+' : '' ?><?= number_format($diff, 1) ?>%</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-sm text-muted-foreground" style="margin-top:0.75rem;">
      <strong>Marktprijs</strong> via Albert Heijn en Open Food Facts; valt terug op ingevoerde prijs. <strong>Verschil</strong> = % verschil tussen marktprijs en ingevoerde prijs.
    </p>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
