<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\OrderRepository;
use App\Repository\SupplierRepository;
use App\Repository\ProductRepository;
use App\Model\Order;

$orderRepo = new OrderRepository();
$supplierRepo = new SupplierRepository();
$productRepo = new ProductRepository();

$suppliers = $supplierRepo->findAll();
$products = $productRepo->findAll();

$csrfValid = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $error = 'Ongeldig token. Probeer opnieuw.';
        $csrfValid = false;
    }

    if ($csrfValid) {
    $order = new Order([
        'supplier_id' => !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null,
        'order_date' => $_POST['order_date'] ?? date('Y-m-d'),
        'status' => 'pending',
        'notes' => trim($_POST['notes'] ?? ''),
    ]);

    $items = [];
    if (isset($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity'])) {
                $product = $productRepo->findById((int)$item['product_id']);
                if ($product) {
                    $items[] = [
                        'product_id' => $product->getId(),
                        'quantity' => (int)$item['quantity'],
                        'unit_price' => (float)$item['unit_price'],
                    ];
                }
            }
        }
    }
    $order->setItems($items);

    if (!empty($items)) {
        $orderId = $orderRepo->create($order);
        flash('success', 'Bestelling #' . $orderId . ' is aangemaakt.');
        header('Location: /orders/index.php');
        exit;
    } else {
        $error = 'Voeg ten minste één product toe.';
        flash('error', $error);
    }
    }
}

$title = 'Nieuwe bestelling';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center gap-3" style="margin-bottom:2rem;">
    <a href="/orders/index.php" class="btn btn-ghost">&larr; Bestellingen</a>
    <h1 class="text-3xl font-semibold tracking-tight">Nieuwe bestelling</h1>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" class="card-form" id="orderForm">
    <?= csrfField() ?>
    <div class="grid" style="grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem;">
      <div class="field">
        <label class="field__label" for="supplier_id">Leverancier</label>
        <select id="supplier_id" name="supplier_id" class="field__input">
          <option value="">-- Selecteer --</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s->getId() ?>"><?= htmlspecialchars($s->getName()) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label class="field__label" for="order_date">Besteldatum</label>
        <input id="order_date" name="order_date" type="date" class="field__input" value="<?= date('Y-m-d') ?>">
      </div>
    </div>

    <div class="field" style="grid-column:1/-1;">
      <label class="field__label" for="notes">Notities</label>
      <textarea id="notes" name="notes" class="field__input" rows="2"></textarea>
    </div>

    <h2 class="text-lg font-semibold" style="margin-bottom:1rem;">Producten</h2>

    <div class="table-wrap" style="margin-bottom:1rem;">
      <table class="data-table" id="itemsTable">
        <thead>
          <tr>
            <th style="width:50%;">Product</th>
            <th style="width:20%;">Aantal</th>
            <th style="width:20%;">Stukprijs (&euro;)</th>
            <th style="width:10%;"></th>
          </tr>
        </thead>
        <tbody>
          <tr class="item-row">
            <td>
              <select name="items[0][product_id]" class="field__input product-select">
                <option value="">-- Kies product --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p->getId() ?>" data-price="<?= $p->getPrice() ?>">
                    <?= htmlspecialchars($p->getName()) ?> (&euro;<?= number_format($p->getPrice(), 2) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td><input type="number" name="items[0][quantity]" class="field__input" min="1" value="1"></td>
            <td><input type="number" name="items[0][unit_price]" class="field__input unit-price" step="0.01" min="0"></td>
            <td><button type="button" class="btn-ghost remove-item" style="padding:0.25rem 0.5rem;color:var(--destructive);">&times;</button></td>
          </tr>
        </tbody>
      </table>
    </div>

    <button type="button" class="btn btn-outline" id="addItem" style="margin-bottom:2rem;">+ Nog een product</button>

    <button type="submit" class="btn btn-primary" id="submitBtn">Bestelling aanmaken</button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('orderForm');
    const submitBtn = document.getElementById('submitBtn');
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Bezig...';
    });

    let itemIndex = 1;

    document.getElementById('addItem').addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const row = document.querySelector('.item-row').cloneNode(true);
        row.innerHTML = row.innerHTML.replace(/items\[0\]/g, 'items[' + itemIndex + ']');
        row.querySelectorAll('input').forEach(inp => inp.value = '');
        row.querySelectorAll('select').forEach(sel => sel.selectedIndex = 0);
        tbody.appendChild(row);
        itemIndex++;
        attachEvents();
    });

    function attachEvents() {
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.removeEventListener('click', handleRemove);
            btn.addEventListener('click', handleRemove);
        });
        document.querySelectorAll('.product-select').forEach(sel => {
            sel.removeEventListener('change', handlePriceFill);
            sel.addEventListener('change', handlePriceFill);
        });
    }

    function handleRemove(e) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            e.target.closest('.item-row').remove();
        }
    }

    function handlePriceFill(e) {
        const sel = e.target;
        const price = sel.options[sel.selectedIndex]?.dataset?.price;
        const row = sel.closest('.item-row');
        if (price && row) {
            row.querySelector('.unit-price').value = price;
        }
    }

    attachEvents();
});
</script>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
