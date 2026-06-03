<?php
require_once __DIR__ . '/../includes/init.php';
requireAuth();

use App\Repository\SupplierRepository;
use App\Model\Supplier;

$repo   = new SupplierRepository();
$errors = [];
$edit   = null;

if (isset($_GET['edit'])) {
    $edit = $repo->findById((int)$_GET['edit']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $action = $_POST['action'] ?? '';

    if (empty($errors) && in_array($action, ['create', 'update'])) {
        $name          = trim($_POST['name'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $address       = trim($_POST['address'] ?? '');

        if (empty($name)) $errors[] = 'Naam is verplicht.';

        if (empty($errors)) {
            if ($action === 'create') {
                $supplier = new Supplier([
                    'name'           => $name,
                    'contact_person' => $contactPerson ?: null,
                    'email'          => $email ?: null,
                    'phone'          => $phone ?: null,
                    'address'        => $address ?: null,
                ]);
                $repo->create($supplier);
                flash('success', 'Leverancier "' . htmlspecialchars($name) . '" is toegevoegd.');
                header('Location: /suppliers/index.php');
                exit;
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $s = $repo->findById($id);
                if ($s) {
                    $s->setName($name);
                    $s->setContactPerson($contactPerson ?: null);
                    $s->setEmail($email ?: null);
                    $s->setPhone($phone ?: null);
                    $s->setAddress($address ?: null);
                    $repo->update($s);
                    flash('success', 'Leverancier "' . htmlspecialchars($name) . '" is bijgewerkt.');
                    header('Location: /suppliers/index.php');
                    exit;
                }
            }
        }
    }

    if (empty($errors) && $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $s = $repo->findById($id);
        if ($s) {
            $repo->delete($id);
            flash('success', 'Leverancier "' . htmlspecialchars($s->getName()) . '" is verwijderd.');
        }
        header('Location: /suppliers/index.php');
        exit;
    }
}

$suppliers = $repo->findAll();
$title = 'Leveranciers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <h1 class="text-3xl font-semibold tracking-tight" style="margin-bottom:1.5rem;">Leveranciers</h1>

  <?php if (!empty($errors)): ?>
    <div class="alert alert--error">
      <ul style="margin:0;padding-left:1.25rem;">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid" style="grid-template-columns:1fr 1.5fr;gap:2rem;">
    <!-- Form -->
    <div class="detail-card">
      <h3 class="detail-card__title"><?= $edit ? 'Leverancier bewerken' : 'Nieuwe leverancier' ?></h3>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit->getId() ?>">
        <?php endif; ?>

        <div class="field" style="margin-bottom:1rem;">
          <label class="field__label" for="name">Naam *</label>
          <input id="name" name="name" type="text" class="field__input" value="<?= htmlspecialchars($_POST['name'] ?? ($edit ? $edit->getName() : '')) ?>" required>
        </div>
        <div class="field" style="margin-bottom:1rem;">
          <label class="field__label" for="contact_person">Contactpersoon</label>
          <input id="contact_person" name="contact_person" type="text" class="field__input" value="<?= htmlspecialchars($_POST['contact_person'] ?? ($edit ? $edit->getContactPerson() ?? '' : '')) ?>">
        </div>
        <div class="field" style="margin-bottom:1rem;">
          <label class="field__label" for="email">E-mail</label>
          <input id="email" name="email" type="email" class="field__input" value="<?= htmlspecialchars($_POST['email'] ?? ($edit ? $edit->getEmail() ?? '' : '')) ?>">
        </div>
        <div class="field" style="margin-bottom:1rem;">
          <label class="field__label" for="phone">Telefoon</label>
          <input id="phone" name="phone" type="text" class="field__input" value="<?= htmlspecialchars($_POST['phone'] ?? ($edit ? $edit->getPhone() ?? '' : '')) ?>">
        </div>
        <div class="field" style="margin-bottom:1.5rem;">
          <label class="field__label" for="address">Adres</label>
          <textarea id="address" name="address" class="field__input" rows="2"><?= htmlspecialchars($_POST['address'] ?? ($edit ? $edit->getAddress() ?? '' : '')) ?></textarea>
        </div>

        <div class="flex gap-2">
          <button type="submit" class="btn btn-primary">
            <?= $edit ? 'Opslaan' : 'Toevoegen' ?>
          </button>
          <?php if ($edit): ?>
            <a href="/suppliers/index.php" class="btn btn-ghost">Annuleren</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- List -->
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Naam</th>
            <th>Contact</th>
            <th>E-mail</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($suppliers)): ?>
            <tr><td colspan="4" class="empty-state">Nog geen leveranciers</td></tr>
          <?php else: ?>
            <?php foreach ($suppliers as $s): ?>
            <tr>
              <td class="font-medium"><?= htmlspecialchars($s->getName()) ?></td>
              <td class="text-sm text-muted-foreground"><?= htmlspecialchars($s->getContactPerson() ?? '-') ?></td>
              <td class="text-sm"><?= htmlspecialchars($s->getEmail() ?? '-') ?></td>
              <td class="actions">
                <a href="/suppliers/index.php?edit=<?= $s->getId() ?>" class="btn-icon" title="Bewerken">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Leverancier &#34;<?= htmlspecialchars($s->getName()) ?>&#34; verwijderen?');">
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
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
