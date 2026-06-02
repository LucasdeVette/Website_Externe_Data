<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\OrderRepository;

$orderRepo = new OrderRepository();
$status    = $_GET['status'] ?? null;

$orders = $orderRepo->findAll($status);

$title = 'Bestellingen';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:1.5rem;">
    <div>
      <h1 class="text-3xl font-semibold tracking-tight">Bestellingen</h1>
      <p class="text-muted-foreground mt-1"><?= count($orders) ?> bestelling<?= count($orders) !== 1 ? 'en' : '' ?> gevonden</p>
    </div>
    <a href="/orders/create.php" class="btn btn-primary">+ Nieuwe bestelling</a>
  </div>

  <div class="orders-status-filters">
    <a href="/orders/index.php" class="btn btn-ghost <?= !$status ? 'btn-ghost--active' : '' ?>" style="<?= !$status ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">Alle</a>
    <a href="/orders/index.php?status=pending" class="btn btn-ghost <?= $status === 'pending' ? 'btn-ghost--active' : '' ?>" style="<?= $status === 'pending' ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">In behandeling</a>
    <a href="/orders/index.php?status=confirmed" class="btn btn-ghost <?= $status === 'confirmed' ? 'btn-ghost--active' : '' ?>" style="<?= $status === 'confirmed' ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">Bevestigd</a>
    <a href="/orders/index.php?status=shipped" class="btn btn-ghost <?= $status === 'shipped' ? 'btn-ghost--active' : '' ?>" style="<?= $status === 'shipped' ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">Verzonden</a>
    <a href="/orders/index.php?status=delivered" class="btn btn-ghost <?= $status === 'delivered' ? 'btn-ghost--active' : '' ?>" style="<?= $status === 'delivered' ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">Afgeleverd</a>
    <a href="/orders/index.php?status=cancelled" class="btn btn-ghost <?= $status === 'cancelled' ? 'btn-ghost--active' : '' ?>" style="<?= $status === 'cancelled' ? 'background:var(--primary);color:var(--primary-foreground);' : '' ?>">Geannuleerd</a>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Datum</th>
          <th>Leverancier</th>
          <th>Artikelen</th>
          <th>Totaal</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr><td colspan="7" class="empty-state">Geen bestellingen gevonden</td></tr>
        <?php else: ?>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td class="font-mono">#<?= $o->getId() ?></td>
            <td class="text-sm"><?= htmlspecialchars($o->getCreatedAt() ? date('d-m-Y H:i', strtotime($o->getCreatedAt())) : '-') ?></td>
            <td><?= htmlspecialchars($o->getSupplierName() ?? 'Onbekend') ?></td>
            <td><?= $o->getItemCount() ?></td>
            <td class="price">&euro;<?= number_format($o->getTotalAmount(), 2) ?></td>
            <td>
              <span class="status-badge status-badge--<?= $o->getStatus() ?>">
                <?= match ($o->getStatus()) {
                    'pending'   => 'In behandeling',
                    'confirmed' => 'Bevestigd',
                    'shipped'   => 'Verzonden',
                    'delivered' => 'Afgeleverd',
                    'cancelled' => 'Geannuleerd',
                    default     => ucfirst($o->getStatus()),
                } ?>
              </span>
            </td>
            <td class="actions">
              <a href="/orders/view.php?id=<?= $o->getId() ?>" class="btn btn-ghost btn-sm">Bekijken</a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
