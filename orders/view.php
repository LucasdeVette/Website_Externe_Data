<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\OrderRepository;

$orderRepo = new OrderRepository();
$id = (int) ($_GET['id'] ?? 0);
$order = $orderRepo->findById($id);

if (!$order) {
    flash('error', 'Bestelling niet gevonden.');
    header('Location: /orders/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        flash('error', 'Ongeldig token. Probeer opnieuw.');
        header('Location: /orders/view.php?id=' . $id);
        exit;
    }
    $allowed = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($_POST['status'], $allowed, true)) {
        flash('error', 'Ongeldige status.');
        header('Location: /orders/view.php?id=' . $id);
        exit;
    }
    $orderRepo->updateStatus($id, $_POST['status']);
    flash('success', 'Status van bestelling #' . $id . ' is bijgewerkt.');
    header('Location: /orders/view.php?id=' . $id);
    exit;
}

$title = 'Bestelling #' . $order->getId();
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:2rem;">
    <div class="flex items-center gap-3">
      <a href="/orders/index.php" class="btn btn-ghost">&larr; Bestellingen</a>
      <h1 class="text-3xl font-semibold tracking-tight">Bestelling #<?= $order->getId() ?></h1>
    </div>
    <form method="POST" class="flex items-center gap-2">
      <?= csrfField() ?>
      <select name="status" class="form-input" style="width:auto;min-width:140px;">
        <option value="pending" <?= $order->getStatus() === 'pending' ? 'selected' : '' ?>>In afwachting</option>
        <option value="confirmed" <?= $order->getStatus() === 'confirmed' ? 'selected' : '' ?>>Bevestigd</option>
        <option value="shipped" <?= $order->getStatus() === 'shipped' ? 'selected' : '' ?>>Verzonden</option>
        <option value="delivered" <?= $order->getStatus() === 'delivered' ? 'selected' : '' ?>>Geleverd</option>
        <option value="cancelled" <?= $order->getStatus() === 'cancelled' ? 'selected' : '' ?>>Geannuleerd</option>
      </select>
      <button type="submit" class="btn btn-primary">Status bijwerken</button>
    </form>
  </div>

  <div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem;">
    <div class="detail-card">
      <h3 class="detail-card__title">Bestelgegevens</h3>
      <dl class="detail-list">
        <dt>Datum</dt>
        <dd><?= htmlspecialchars($order->getOrderDate()) ?></dd>
        <dt>Status</dt>
        <dd><span class="badge badge--<?= $order->getStatus() === 'delivered' ? 'success' : ($order->getStatus() === 'cancelled' ? 'danger' : 'warning') ?>"><?= $order->getStatusLabel() ?></span></dd>
        <dt>Notities</dt>
        <dd><?= nl2br(htmlspecialchars($order->getNotes() ?? '-')) ?></dd>
      </dl>
      <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);">
        <form method="POST" action="/orders/delete.php" onsubmit="return confirm('Bestelling #<?= $order->getId() ?> verwijderen?');">
          <?= csrfField() ?>
          <input type="hidden" name="id" value="<?= $order->getId() ?>">
          <button type="submit" class="btn btn-ghost" style="color:var(--destructive);font-size:0.8rem;">Bestelling verwijderen</button>
        </form>
      </div>
    </div>
    <div class="detail-card">
      <h3 class="detail-card__title">Leverancier</h3>
      <?php if ($order->getSupplier()): ?>
        <dl class="detail-list">
          <dt>Naam</dt>
          <dd><a href="/suppliers/index.php?edit=<?= $order->getSupplier()->getId() ?>" style="color:var(--primary);"><?= htmlspecialchars($order->getSupplier()->getName()) ?></a></dd>
          <dt>Contact</dt>
          <dd><?= htmlspecialchars($order->getSupplier()->getContactPerson() ?? '-') ?></dd>
          <dt>E-mail</dt>
          <dd><?= htmlspecialchars($order->getSupplier()->getEmail() ?? '-') ?></dd>
        </dl>
      <?php else: ?>
        <p class="text-muted-foreground">Geen leverancier</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>Product</th>
          <th>Aantal</th>
          <th>Stukprijs</th>
          <th>Totaal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($order->getItems() as $item): ?>
        <tr>
          <td><a href="/products/edit.php?id=<?= $item['product_id'] ?>" style="color:var(--foreground);text-decoration:none;"><?= htmlspecialchars($item['product_name']) ?></a></td>
          <td><?= $item['quantity'] ?></td>
          <td>&euro;<?= number_format($item['unit_price'], 2) ?></td>
          <td>&euro;<?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="font-weight:600;">
          <td colspan="3" style="text-align:right;">Totaal</td>
          <td>&euro;<?= number_format($order->getTotalAmount(), 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<style>
.detail-card { background: var(--background); border: 1px solid var(--border); border-radius: calc(var(--radius) + 0.25rem); padding: 1.5rem; }
.detail-card__title { font-size: 0.875rem; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1rem; }
.detail-list { display: grid; grid-template-columns: auto 1fr; gap: 0.5rem 1rem; font-size: 0.875rem; }
.detail-list dt { color: var(--muted-foreground); font-weight: 500; }
.detail-list dd { color: var(--foreground); }
.table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: var(--radius); background: var(--background); }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: var(--muted-foreground); border-bottom: 1px solid var(--border); background: var(--secondary); }
.data-table td, .data-table tfoot td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); color: var(--foreground); }
.data-table tr:last-child td { border-bottom: none; }
.data-table tfoot td { border-top: 2px solid var(--border); }
.badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; }
.badge--success { background: color-mix(in srgb, #22c55e 15%, transparent); color: #166534; }
.badge--warning { background: color-mix(in srgb, #f59e0b 15%, transparent); color: #92400e; }
.badge--danger { background: color-mix(in srgb, #ef4444 15%, transparent); color: #991b1b; }
.form-input { padding: 0.5rem 0.75rem; border-radius: var(--radius); border: 1px solid var(--border); background: var(--background); color: var(--foreground); font-size: 0.875rem; outline: none; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
