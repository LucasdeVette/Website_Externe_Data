<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/auth.php';

use App\Repository\CategoryRepository;
use App\Model\Category;

$repo   = new CategoryRepository();
$errors = [];
$edit   = null;

// --- Edit mode ---
if (isset($_GET['edit'])) {
    $edit = $repo->findById((int)$_GET['edit']);
}

// --- Handle POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $action = $_POST['action'] ?? '';

    // CREATE / UPDATE
    if (empty($errors) && in_array($action, ['create', 'update'])) {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $errors[] = 'Naam is verplicht.';
        } elseif ($action === 'create') {
            $category = new Category(['name' => $name, 'description' => $desc ?: null]);
            $repo->create($category);
            flash('success', 'Categorie "' . htmlspecialchars($name) . '" is toegevoegd.');
            header('Location: /categories/index.php');
            exit;
        } else {
            $id = (int)($_POST['id'] ?? 0);
            $cat = $repo->findById($id);
            if ($cat) {
                $cat->setName($name);
                $cat->setDescription($desc ?: null);
                $repo->update($cat);
                flash('success', 'Categorie "' . htmlspecialchars($name) . '" is bijgewerkt.');
                header('Location: /categories/index.php');
                exit;
            }
        }
    }

    // DELETE
    if (empty($errors) && $action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $cat = $repo->findById($id);
        if ($cat) {
            $repo->delete($id);
            flash('success', 'Categorie "' . htmlspecialchars($cat->getName()) . '" is verwijderd.');
        }
        header('Location: /categories/index.php');
        exit;
    }
}

$categories = $repo->findAll();
$title = 'Categorieën';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <h1 class="text-3xl font-semibold tracking-tight" style="margin-bottom:1.5rem;">Categorieën</h1>

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
      <h3 class="detail-card__title"><?= $edit ? 'Categorie bewerken' : 'Nieuwe categorie' ?></h3>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?>
          <input type="hidden" name="id" value="<?= $edit->getId() ?>">
        <?php endif; ?>

        <div class="form-group" style="margin-bottom:1rem;">
          <label class="form-label" for="name">Naam *</label>
          <input id="name" name="name" type="text" class="form-input" value="<?= htmlspecialchars($_POST['name'] ?? ($edit ? $edit->getName() : '')) ?>" required>
        </div>

        <div class="form-group" style="margin-bottom:1.5rem;">
          <label class="form-label" for="description">Beschrijving</label>
          <textarea id="description" name="description" class="form-input" rows="3"><?= htmlspecialchars($_POST['description'] ?? ($edit ? $edit->getDescription() ?? '' : '')) ?></textarea>
        </div>

        <div class="flex gap-2">
          <button type="submit" class="btn btn-primary">
            <?= $edit ? 'Opslaan' : 'Toevoegen' ?>
          </button>
          <?php if ($edit): ?>
            <a href="/categories/index.php" class="btn btn-ghost">Annuleren</a>
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
            <th>Beschrijving</th>
            <th>Producten</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
            <tr><td colspan="4" class="empty-state">Nog geen categorieën</td></tr>
          <?php else: ?>
            <?php foreach ($categories as $cat): ?>
            <tr>
              <td class="font-medium"><?= htmlspecialchars($cat->getName()) ?></td>
              <td class="text-muted-foreground text-sm"><?= htmlspecialchars($cat->getDescription() ?? '-') ?></td>
              <td><?= $repo->productCount($cat->getId()) ?></td>
              <td class="actions">
                <a href="/categories/index.php?edit=<?= $cat->getId() ?>" class="btn-icon" title="Bewerken">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </a>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Categorie &#34;<?= htmlspecialchars($cat->getName()) ?>&#34; verwijderen?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $cat->getId() ?>">
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

<style>
.detail-card { background: var(--background); border: 1px solid var(--border); border-radius: calc(var(--radius) + 0.25rem); padding: 1.5rem; }
.detail-card__title { font-size: 0.875rem; font-weight: 600; color: var(--muted-foreground); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 500; color: var(--foreground); }
.form-input { width: 100%; padding: 0.6rem 0.75rem; border-radius: var(--radius); border: 1px solid var(--border); background: var(--background); color: var(--foreground); font-size: 0.875rem; outline: none; font-family: inherit; }
.form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px color-mix(in srgb, var(--primary) 20%, transparent); }
textarea.form-input { resize: vertical; }
.table-wrap { overflow-x: auto; border: 1px solid var(--border); border-radius: var(--radius); background: var(--background); }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { text-align: left; padding: 0.75rem 1rem; font-weight: 600; color: var(--muted-foreground); border-bottom: 1px solid var(--border); background: var(--secondary); white-space: nowrap; }
.data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: color-mix(in srgb, var(--secondary) 40%, transparent); }
.empty-state { text-align: center; padding: 3rem !important; color: var(--muted-foreground); }
.font-medium { font-weight: 500; }
.text-muted-foreground { color: var(--muted-foreground); }
.text-sm { font-size: 0.8rem; }
.actions { white-space: nowrap; }
.btn-icon { display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: var(--radius); color: var(--muted-foreground); transition: all 0.15s; }
.btn-icon:hover { background: var(--secondary); color: var(--foreground); }
.btn-icon--danger:hover { background: #fef2f2; color: #dc2626; }
.alert { padding: 0.75rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.875rem; }
.alert--error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
@media (max-width: 768px) { .grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
