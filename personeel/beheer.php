<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/_personeel.php';
requireAuth();

if (($_SESSION['username'] ?? '') !== 'admin') {
    header('Location: /personeel/index.php');
    exit;
}

use App\Repository\UserRepository;
use App\Repository\WorkShiftRepository;
use App\Model\User;
use App\Model\WorkShift;

$userRepo     = new UserRepository();
$shiftRepo    = new WorkShiftRepository();
$users        = $userRepo->findAll();
$errors       = [];
$selectedUser = null;
$editUser     = null;
$editShift    = null;

$week = isset($_GET['week']) ? (int)$_GET['week'] : (int)date('W');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('o');
[$week, $year] = semaine($week, $year);

$selectedUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$showAllWeeks   = isset($_GET['all_weeks']);

if ($selectedUserId) {
    $selectedUser = $userRepo->findById($selectedUserId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $action = $_POST['action'] ?? '';

    // Create / Update user
    if (empty($errors) && in_array($action, ['create_user', 'update_user'])) {
        $username    = trim($_POST['username'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $password    = $_POST['password'] ?? '';

        if (empty($username))    $errors[] = 'Gebruikersnaam is verplicht.';
        if (empty($displayName)) $errors[] = 'Weergavenaam is verplicht.';
        if ($action === 'create_user' && empty($password)) $errors[] = 'Wachtwoord is verplicht.';

        if (empty($errors)) {
            if ($action === 'create_user') {
                $existing = $userRepo->findByUsername($username);
                if ($existing) {
                    $errors[] = 'Gebruikersnaam bestaat al.';
                } else {
                    $user = new User([
                        'username'      => $username,
                        'display_name'  => $displayName,
                        'email'         => $email ?: null,
                        'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    ]);
                    $userRepo->create($user);
                    flash('success', 'Medewerker "' . htmlspecialchars($displayName) . '" is toegevoegd.');
                    header("Location: /personeel/beheer.php?user_id=$selectedUserId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : ''));
                    exit;
                }
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $existing = $userRepo->findByUsername($username);
                if ($existing && $existing->getId() !== $id) {
                    $errors[] = 'Gebruikersnaam bestaat al.';
                } else {
                    $user = new User([
                        'id'            => $id,
                        'username'      => $username,
                        'display_name'  => $displayName,
                        'email'         => $email ?: null,
                    ]);
                    $userRepo->update($user);
                    if (!empty($password)) {
                        $userRepo->updatePassword($id, password_hash($password, PASSWORD_BCRYPT));
                    }
                    flash('success', 'Medewerker "' . htmlspecialchars($displayName) . '" is bijgewerkt.');
                    header("Location: /personeel/beheer.php?user_id=$selectedUserId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : ''));
                    exit;
                }
            }
        }
    }

    // Delete user
    if (empty($errors) && $action === 'delete_user') {
        $id = (int)($_POST['id'] ?? 0);
        $user = $userRepo->findById($id);
        if ($user && $user->getUsername() !== 'admin') {
            $userRepo->delete($id);
            flash('success', 'Medewerker is verwijderd.');
        }
        header("Location: /personeel/beheer.php?week=$week&year=$year");
        exit;
    }

    // Create shift for user
    if (empty($errors) && $action === 'create_shift') {
        $userId    = (int)($_POST['user_id'] ?? 0);
        $shiftDate = trim($_POST['shift_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime   = trim($_POST['end_time'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        if (empty($userId))    $errors[] = 'Medewerker is verplicht.';
        if (empty($shiftDate)) $errors[] = 'Datum is verplicht.';
        if (empty($startTime)) $errors[] = 'Begintijd is verplicht.';
        if (empty($endTime))   $errors[] = 'Eindtijd is verplicht.';
        if ($startTime >= $endTime) $errors[] = 'Eindtijd moet na begintijd liggen.';

        if (empty($errors)) {
            $shift = new WorkShift([
                'user_id'    => $userId,
                'shift_date' => $shiftDate,
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'notes'      => $notes ?: null,
            ]);
            $shiftRepo->create($shift);
            flash('success', 'Dienst is toegevoegd.');
            header("Location: /personeel/beheer.php?user_id=$userId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : ''));
            exit;
        }
    }

    // Delete shift
    if (empty($errors) && $action === 'delete_shift') {
        $id = (int)($_POST['id'] ?? 0);
        $shiftRepo->delete($id);
        flash('success', 'Dienst is verwijderd.');
        header("Location: /personeel/beheer.php?user_id=$selectedUserId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : ''));
        exit;
    }

    // Update shift
    if (empty($errors) && $action === 'update') {
        $id        = (int)($_POST['id'] ?? 0);
        $userId    = (int)($_POST['user_id'] ?? 0);
        $shiftDate = trim($_POST['shift_date'] ?? '');
        $startTime = trim($_POST['start_time'] ?? '');
        $endTime   = trim($_POST['end_time'] ?? '');
        $notes     = trim($_POST['notes'] ?? '');

        if (empty($userId))    $errors[] = 'Medewerker is verplicht.';
        if (empty($shiftDate)) $errors[] = 'Datum is verplicht.';
        if (empty($startTime)) $errors[] = 'Begintijd is verplicht.';
        if (empty($endTime))   $errors[] = 'Eindtijd is verplicht.';
        if ($startTime >= $endTime) $errors[] = 'Eindtijd moet na begintijd liggen.';

        if (empty($errors)) {
            $shift = new WorkShift([
                'id'         => $id,
                'user_id'    => $userId,
                'shift_date' => $shiftDate,
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'notes'      => $notes ?: null,
            ]);
            $shiftRepo->update($shift);
            flash('success', 'Dienst is bijgewerkt.');
            header("Location: /personeel/beheer.php?user_id=$selectedUserId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : ''));
            exit;
        }
    }

    // Get edit user
    if ($action === 'edit_user') {
        $editUser = $userRepo->findById((int)($_POST['id'] ?? 0));
    }
}

if (isset($_GET['edit_user'])) {
    $editUser = $userRepo->findById((int)$_GET['edit_user']);
}

if (isset($_GET['edit_shift'])) {
    $editShift = $shiftRepo->findById((int)$_GET['edit_shift']);
}

$monday = (new DateTime())->setISODate($year, $week, 1);
$monday->setTime(0, 0, 0);

// Shifts for selected user
$userShifts = [];
$userWeeks  = [];
if ($selectedUser) {
    if ($showAllWeeks) {
        $userShifts = $shiftRepo->findByDateRange(
            (new DateTime())->setISODate($year, 1, 1)->format('Y-m-d'),
            (new DateTime())->setISODate($year, 12, 31)->format('Y-m-d')
        );
        $userShifts = array_filter($userShifts, fn($s) => $s->getUserId() === $selectedUserId);
        foreach ($userShifts as $s) {
            $w = (int)(new DateTime($s->getShiftDate()))->format('W');
            $wy = (int)(new DateTime($s->getShiftDate()))->format('o');
            $userWeeks[$wy . '-W' . str_pad($w, 2, '0', STR_PAD_LEFT)][] = $s;
        }
        ksort($userWeeks);
    } else {
        $sunday = clone $monday;
        $sunday->modify('+6 days');
        $allShifts = $shiftRepo->findByDateRange($monday->format('Y-m-d'), $sunday->format('Y-m-d'));
        $userShifts = array_filter($allShifts, fn($s) => $s->getUserId() === $selectedUserId);
    }
}

$userColorMap = build_user_color_map($users);

$prevWeek = $week - 1;
$prevYear = $year;
[$prevWeek, $prevYear] = semaine($prevWeek, $prevYear);
$nextWeek = $week + 1;
$nextYear = $year;
[$nextWeek, $nextYear] = semaine($nextWeek, $nextYear);

$title = 'Beheer';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:1.5rem;">
    <h1 class="text-3xl font-semibold tracking-tight">Medewerkers beheren</h1>
    <a href="/personeel/index.php" class="btn btn-ghost btn-sm">Naar rooster</a>
  </div>

  <?php render_errors($errors); ?>

  <!-- User form -->
  <div class="detail-card" style="margin-bottom:1.5rem;">
    <h3 class="detail-card__title"><?= $editUser ? 'Medewerker bewerken' : 'Nieuwe medewerker' ?></h3>
    <form method="POST" class="form-stack">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="<?= $editUser ? 'update_user' : 'create_user' ?>">
      <?php if ($editUser): ?>
        <input type="hidden" name="id" value="<?= $editUser->getId() ?>">
      <?php endif; ?>
      <div class="grid" style="grid-template-columns:1fr 1fr 1fr;gap:1rem;">
        <div class="field">
          <label class="field__label" for="username">Gebruikersnaam *</label>
          <input id="username" name="username" type="text" class="field__input" required
            value="<?= htmlspecialchars($_POST['username'] ?? ($editUser ? $editUser->getUsername() : '')) ?>">
        </div>
        <div class="field">
          <label class="field__label" for="display_name">Weergavenaam *</label>
          <input id="display_name" name="display_name" type="text" class="field__input" required
            value="<?= htmlspecialchars($_POST['display_name'] ?? ($editUser ? $editUser->getDisplayName() : '')) ?>">
        </div>
        <div class="field">
          <label class="field__label" for="email">E-mail</label>
          <input id="email" name="email" type="email" class="field__input"
            value="<?= htmlspecialchars($_POST['email'] ?? ($editUser ? $editUser->getEmail() ?? '' : '')) ?>">
        </div>
      </div>
      <div class="field" style="max-width:300px;">
        <label class="field__label" for="password">Wachtwoord <?= $editUser ? '(leeg laten om te behouden)' : '*' ?></label>
        <input id="password" name="password" type="password" class="field__input" <?= $editUser ? '' : 'required' ?>>
      </div>
      <div class="flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $editUser ? 'Opslaan' : 'Toevoegen' ?></button>
        <?php if ($editUser): ?>
          <a href="/personeel/beheer.php?user_id=<?= $selectedUserId ?>&week=<?= $week ?>&year=<?= $year ?>" class="btn btn-ghost">Annuleren</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Employee grid -->
  <div class="grid" style="grid-template-columns:1fr 1.5fr;gap:2rem;">
    <!-- User list -->
    <div>
      <div class="detail-card">
        <h3 class="detail-card__title">Alle medewerkers (<?= count($users) ?>)</h3>
        <div style="display:flex;flex-direction:column;gap:0.25rem;">
          <?php foreach ($users as $u):
            $color = $userColorMap[$u->getId()] ?? '#666';
            $isSelected = $selectedUser && $selectedUser->getId() === $u->getId();
          ?>
            <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.5rem;border-radius:var(--radius);<?= $isSelected ? 'background:color-mix(in srgb, var(--primary) 8%, transparent);' : '' ?>border-bottom:1px solid var(--border);">
              <?= colored_dot($color, 10) ?>
              <a href="/personeel/beheer.php?user_id=<?= $u->getId() ?>&week=<?= $week ?>&year=<?= $year ?>" style="flex:1;text-decoration:none;color:var(--foreground);font-weight:<?= $isSelected ? '600' : '400' ?>;">
                <?= htmlspecialchars($u->getDisplayName()) ?>
                <span class="text-muted-foreground" style="font-size:0.75rem;">(@<?= htmlspecialchars($u->getUsername()) ?>)</span>
              </a>
              <?php if ($u->getUsername() !== 'admin'): ?>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Medewerker <?= htmlspecialchars($u->getDisplayName()) ?> verwijderen?');">
                  <?= csrfField() ?>
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?= $u->getId() ?>">
                  <button type="submit" class="btn-icon btn-icon--danger" title="Verwijderen"><?= icon_delete(13) ?></button>
                </form>
                <a href="/personeel/beheer.php?edit_user=<?= $u->getId() ?>&user_id=<?= $selectedUserId ?>&week=<?= $week ?>&year=<?= $year ?>" class="btn-icon" title="Bewerken"><?= icon_edit(13) ?></a>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Selected user shifts -->
    <div>
      <?php if ($selectedUser): ?>
        <div class="detail-card" style="margin-bottom:1rem;">
          <h3 class="detail-card__title">
            Diensten voor <?= htmlspecialchars($selectedUser->getDisplayName()) ?>
          </h3>

          <?php if (!$showAllWeeks): ?>
          <div class="flex items-center justify-between" style="margin-bottom:1rem;">
            <a href="/personeel/beheer.php?user_id=<?= $selectedUserId ?>&week=<?= $prevWeek ?>&year=<?= $prevYear ?>" class="btn btn-ghost btn-sm">&#8592;</a>
            <span style="font-weight:600;font-size:0.9rem;">Week <?= $week ?></span>
            <a href="/personeel/beheer.php?user_id=<?= $selectedUserId ?>&week=<?= $nextWeek ?>&year=<?= $nextYear ?>" class="btn btn-ghost btn-sm">&#8594;</a>
          </div>
          <?php endif; ?>

          <div style="display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap;">
            <a href="/personeel/beheer.php?user_id=<?= $selectedUserId ?>&week=<?= date('W') ?>&year=<?= date('o') ?>" class="btn btn-ghost btn-sm<?= !$showAllWeeks ? '' : '' ?>">Deze week</a>
            <a href="/personeel/beheer.php?user_id=<?= $selectedUserId ?>&all_weeks=1" class="btn btn-ghost btn-sm<?= $showAllWeeks ? ' btn-active' : '' ?>">Alle weken</a>
          </div>

          <?php if ($showAllWeeks): ?>
            <?php if (empty($userWeeks)): ?>
              <p class="text-sm text-muted-foreground">Geen diensten dit jaar.</p>
            <?php else: ?>
              <?php foreach ($userWeeks as $weekKey => $weekShifts): ?>
                <div style="margin-bottom:1rem;border-bottom:1px solid var(--border);padding-bottom:0.75rem;">
                  <div style="font-weight:600;font-size:0.8rem;color:var(--muted-foreground);margin-bottom:0.4rem;"><?= $weekKey ?></div>
                  <?php foreach ($weekShifts as $s):
                    $startT = substr($s->getStartTime(), 0, 5);
                    $endT   = substr($s->getEndTime(), 0, 5);
                    $durStr = $s->getDurationFormatted();
                  ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.25rem 0;font-size:0.85rem;">
                      <span><?= (new DateTime($s->getShiftDate()))->format('d-m-Y') ?> &middot; <?= $startT ?>-<?= $endT ?> (<?= $durStr ?>)</span>
                      <span style="display:inline-flex;gap:0.25rem;">
                      <a href="/personeel/beheer.php?edit_shift=<?= $s->getId() ?>&user_id=<?= $selectedUserId ?>&week=<?= $week ?>&year=<?= $year ?><?= $showAllWeeks ? '&all_weeks=1' : '' ?>" class="btn-icon" title="Bewerken"><?= icon_edit(12) ?></a>
                      <?= delete_shift_form($s->getId(), 12) ?>
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          <?php else: ?>
            <div class="table-wrap" style="margin:0 -1rem;">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Datum</th>
                    <th>Begin</th>
                    <th>Eind</th>
                    <th>Duur</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $weekHasShifts = false;
                  for ($d = 0; $d < 7; $d++):
                    $date = clone $monday;
                    $date->modify("+$d days");
                    $dateStr = $date->format('Y-m-d');
                    $dayShifts = array_filter($userShifts, fn($s) => $s->getShiftDate() === $dateStr);
                  ?>
                    <?php if (!empty($dayShifts)): $weekHasShifts = true; ?>
                      <?php foreach ($dayShifts as $s):
                        $startT = substr($s->getStartTime(), 0, 5);
                        $endT   = substr($s->getEndTime(), 0, 5);
                        $durStr = $s->getDurationFormatted();
                      ?>
                        <tr>
                          <td><?= $date->format('d-m-Y') ?></td>
                          <td class="text-muted-foreground"><?= $startT ?></td>
                          <td class="text-muted-foreground"><?= $endT ?></td>
                          <td><?= $durStr ?></td>
                          <td class="actions">
                            <a href="/personeel/beheer.php?edit_shift=<?= $s->getId() ?>&user_id=<?= $selectedUserId ?>&week=<?= $week ?>&year=<?= $year ?>" class="btn-icon" title="Bewerken"><?= icon_edit(13) ?></a>
                            <?= delete_shift_form($s->getId(), 13) ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  <?php endfor; ?>
                  <?php if (!$weekHasShifts): ?>
                    <tr><td colspan="5" class="empty-state">Geen diensten deze week</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>

          <?php
          $cancelParams = "user_id=$selectedUserId&week=$week&year=$year" . ($showAllWeeks ? '&all_weeks=1' : '');
          ?>

          <?php if ($editShift): ?>
          <div class="detail-card" style="margin-bottom:1rem;">
            <h4 class="detail-card__title" style="font-size:0.85rem;">Dienst bewerken</h4>
            <?php
            $shift = $editShift;
            $action = 'update';
            $showUserSelect = true;
            $defaultDate = '';
            $cancelUrl = '/personeel/beheer.php?' . $cancelParams;
            require __DIR__ . '/../includes/_shift_form.php';
            ?>
          </div>
          <?php endif; ?>

          <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);">
            <h4 style="font-size:0.85rem;font-weight:600;margin-bottom:0.75rem;">Dienst toevoegen voor <?= htmlspecialchars($selectedUser->getDisplayName()) ?></h4>
            <?php
            $shift = null;
            $action = 'create_shift';
            $showUserSelect = false;
            $selectedUserId = $selectedUser->getId();
            $defaultDate = $monday->format('Y-m-d');
            $cancelUrl = null;
            require __DIR__ . '/../includes/_shift_form.php';
            ?>
          </div>
        </div>
      <?php else: ?>
        <div class="detail-card">
          <p class="text-sm text-muted-foreground" style="margin:0;">Selecteer een medewerker om diensten te beheren.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateParam = new URLSearchParams(window.location.search).get('date');
    if (dateParam) {
        var input = document.getElementById('shift_date');
        if (input) input.value = dateParam;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
