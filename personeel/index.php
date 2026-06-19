<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/_personeel.php';
requireAuth();

use App\Repository\WorkShiftRepository;
use App\Repository\UserRepository;

$shiftRepo = new WorkShiftRepository();
$userRepo  = new UserRepository();
$users     = $userRepo->findAll();
$errors    = [];

$week = isset($_GET['week']) ? (int)$_GET['week'] : (int)date('W');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('o');
[$week, $year] = semaine($week, $year);

$isAdmin = ($_SESSION['username'] ?? '') === 'admin';

$edit = null;
if ($isAdmin && isset($_GET['edit'])) {
    $edit = $shiftRepo->findById((int)$_GET['edit']);
}

$userColorMap = build_user_color_map($users);

// Calculate Monday and Sunday of the selected week
$monday = (new DateTime())->setISODate($year, $week, 1);
$monday->setTime(0, 0, 0);
$sunday = clone $monday;
$sunday->modify('+6 days');

$weekDates = [];
for ($d = 0; $d < 7; $d++) {
    $date = clone $monday;
    $date->modify("+$d days");
    $weekDates[] = $date;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Ongeldig token. Probeer opnieuw.';
    }

    $action = $_POST['action'] ?? '';

    if (empty($errors) && $action === 'delete_day' && $isAdmin) {
        $date = trim($_POST['date'] ?? '');
        if ($date) {
            $shiftRepo->deleteByDate($date);
            flash('success', 'Alle diensten voor ' . date('d-m-Y', strtotime($date)) . ' zijn verwijderd.');
        }
        header("Location: /personeel/index.php?week=$week&year=$year");
        exit;
    }

    if (empty($errors) && $action === 'delete' && $isAdmin) {
        $id = (int)($_POST['id'] ?? 0);
        $shiftRepo->delete($id);
        flash('success', 'Dienst is verwijderd.');
        header("Location: /personeel/index.php?week=$week&year=$year");
        exit;
    }

    if (empty($errors) && $action === 'update' && $isAdmin) {
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
            $shift = new \App\Model\WorkShift([
                'id'         => $id,
                'user_id'    => $userId,
                'shift_date' => $shiftDate,
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'notes'      => $notes ?: null,
            ]);
            $shiftRepo->update($shift);
            flash('success', 'Dienst is bijgewerkt.');
            header("Location: /personeel/index.php?week=$week&year=$year");
            exit;
        }
    }
}

$shifts = $shiftRepo->findByDateRange($monday->format('Y-m-d'), $sunday->format('Y-m-d'));

$shiftsByDay = [];
foreach ($shifts as $s) {
    $shiftsByDay[$s->getShiftDate()][] = $s;
}

$prevWeek = $week - 1;
$prevYear = $year;
[$prevWeek, $prevYear] = semaine($prevWeek, $prevYear);
$nextWeek = $week + 1;
$nextYear = $year;
[$nextWeek, $nextYear] = semaine($nextWeek, $nextYear);

$dayNames = ['Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag'];

$weekTotals = [];
foreach ($shifts as $s) {
    $uid = $s->getUserId();
    if (!isset($weekTotals[$uid])) {
        $weekTotals[$uid] = [
            'name'   => $s->getDisplayName() ?? 'Onbekend',
            'hours'  => 0,
            'shifts' => 0,
        ];
    }
    $weekTotals[$uid]['hours']  += $s->getDurationHours();
    $weekTotals[$uid]['shifts'] += 1;
}

$title = 'Personeel';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="max-w-5xl px-6" style="padding-top:2rem;padding-bottom:4rem;">
  <div class="flex items-center justify-between" style="margin-bottom:1.5rem;">
    <h1 class="text-3xl font-semibold tracking-tight">Personeelsrooster</h1>
    <div class="flex gap-2">
      <button id="toggleAllBtn" class="btn btn-ghost btn-sm">Alles openen</button>
      <a href="/personeel/index.php" class="btn btn-ghost btn-sm">Deze week</a>
      <?php if ($isAdmin): ?>
        <a href="/personeel/beheer.php" class="btn btn-ghost btn-sm">Beheer</a>
      <?php endif; ?>
    </div>
  </div>

  <?php render_errors($errors); ?>

  <!-- Week navigation -->
  <div class="flex items-center justify-between" style="margin-bottom:1.5rem;">
    <a href="/personeel/index.php?week=<?= $prevWeek ?>&year=<?= $prevYear ?>" class="btn btn-ghost btn-sm">&#8592; Vorige week</a>
    <span style="font-weight:700;font-size:1.15rem;">Week <?= $week ?> &mdash; <?= $monday->format('d-m-Y') ?> t/m <?= $sunday->format('d-m-Y') ?></span>
    <a href="/personeel/index.php?week=<?= $nextWeek ?>&year=<?= $nextYear ?>" class="btn btn-ghost btn-sm">Volgende week &#8594;</a>
  </div>

  <?php if ($edit): ?>
  <div class="detail-card" style="margin-bottom:1.5rem;">
    <h3 class="detail-card__title">Dienst bewerken</h3>
    <?php
    $shift = $edit;
    $action = 'update';
    $showUserSelect = true;
    $selectedUserId = 0;
    $defaultDate = '';
    $cancelUrl = '/personeel/index.php?week=' . $week . '&year=' . $year;
    require __DIR__ . '/../includes/_shift_form.php';
    ?>
  </div>
  <?php endif; ?>

  <!-- Day accordion -->
  <div class="day-accordion">
    <?php foreach ($weekDates as $dateObj):
      $dateStr  = $dateObj->format('Y-m-d');
      $dayName  = $dayNames[(int)$dateObj->format('N') - 1];
      $dayShifts = $shiftsByDay[$dateStr] ?? [];
      $isToday  = $dateStr === date('Y-m-d');
      $dayTotalHours = 0;
      foreach ($dayShifts as $s) {
        $dayTotalHours += $s->getDurationHours();
      }
    ?>
    <details class="day-card <?= $isToday ? 'day-card--today' : '' ?>" <?= $isToday ? : '' ?>>
      <summary class="day-card__summary">
        <div class="day-card__header">
          <div class="day-card__info">
            <span class="day-card__dayname"><?= $dayName ?></span>
            <span class="day-card__date"><?= $dateObj->format('d-m-Y') ?></span>
          </div>
          <div class="day-card__meta">
            <?php if (!empty($dayShifts)): ?>
              <span class="day-card__count"><?= count($dayShifts) ?> diensten</span>
              <span class="day-card__hours"><?= number_format($dayTotalHours, 1, ',', '') ?> u</span>
            <?php else: ?>
              <span class="day-card__count day-card__count--none">Geen diensten</span>
            <?php endif; ?>
            <span class="day-card__arrow">&#9660;</span>
          </div>
        </div>
      </summary>
      <div class="day-card__body">
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Medewerker</th>
                <th>Begin</th>
                <th>Eind</th>
                <th>Duur</th>
                <th>Notities</th>
                <?php if ($isAdmin): ?><th></th><?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($dayShifts)): ?>
                <tr><td colspan="<?= $isAdmin ? 6 : 5 ?>" class="empty-state">Geen diensten op deze dag</td></tr>
              <?php else: ?>
                <?php foreach ($dayShifts as $s):
                  $color  = $userColorMap[$s->getUserId()] ?? '#2563eb';
                  $startT = substr($s->getStartTime(), 0, 5);
                  $endT   = substr($s->getEndTime(), 0, 5);
                  $durStr = $s->getDurationFormatted();
                ?>
                <tr>
                  <td>
                      <span style="display:inline-flex;align-items:center;gap:0.35rem;">
                      <?= colored_dot($color, 8) ?>
                      <?= htmlspecialchars($s->getDisplayName() ?? '') ?>
                    </span>
                  </td>
                  <td class="text-muted-foreground"><?= $startT ?></td>
                  <td class="text-muted-foreground"><?= $endT ?></td>
                  <td><?= $durStr ?></td>
                  <td class="text-muted-foreground"><?= htmlspecialchars($s->getNotes() ?? '-') ?></td>
                  <?php if ($isAdmin): ?>
                  <td class="actions">
                    <a href="/personeel/index.php?edit=<?= $s->getId() ?>&week=<?= $week ?>&year=<?= $year ?>" class="btn-icon" title="Bewerken"><?= icon_edit(15) ?></a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Dienst verwijderen?');">
                      <?= csrfField() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $s->getId() ?>">
                      <button type="submit" class="btn-icon btn-icon--danger" title="Verwijderen"><?= icon_delete(15) ?></button>
                    </form>
                  </td>
                  <?php endif; ?>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($isAdmin && !empty($dayShifts)): ?>
          <form method="POST" style="margin-top:0.75rem;" onsubmit="return confirm('Alle diensten voor <?= $dateObj->format('d-m-Y') ?> verwijderen?');">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_day">
            <input type="hidden" name="date" value="<?= $dateStr ?>">
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--destructive);">Dag verwijderen</button>
          </form>
        <?php endif; ?>
      </div>
    </details>
    <?php endforeach; ?>
  </div>

  <div class="grid" style="grid-template-columns:1fr 2fr;gap:2rem;margin-top:2rem;">
    <div class="detail-card">
      <h3 class="detail-card__title">Alle medewerkers (<?= count($users) ?>)</h3>
      <div style="display:flex;flex-direction:column;gap:0.25rem;">
        <?php foreach ($users as $u):
          $color = $userColorMap[$u->getId()] ?? '#666';
        ?>
          <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.5rem;border-radius:var(--radius);border-bottom:1px solid var(--border);">
            <?= colored_dot($color, 10) ?>
            <a href="/personeel/beheer.php?user_id=<?= $u->getId() ?>&week=<?= $week ?>&year=<?= $year ?>" style="flex:1;text-decoration:none;color:var(--foreground);">
              <?= htmlspecialchars($u->getDisplayName()) ?>
              <span class="text-muted-foreground" style="font-size:0.75rem;">(@<?= htmlspecialchars($u->getUsername()) ?>)</span>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!empty($weekTotals)): ?>
    <div class="detail-card">
      <h3 class="detail-card__title">Urenoverzicht week <?= $week ?></h3>
      <div style="display:flex;flex-direction:column;gap:0.4rem;">
        <?php
        uasort($weekTotals, fn($a, $b) => $b['hours'] <=> $a['hours']);
        $grandTotal = 0;
        ?>
        <?php foreach ($weekTotals as $uid => $data): ?>
          <?php $color = $userColorMap[$uid] ?? '#666'; ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:0.3rem 0;border-bottom:1px solid var(--border);font-size:0.85rem;">
            <div style="display:flex;align-items:center;gap:0.4rem;">
              <?= colored_dot($color, 8) ?>
              <span><?= htmlspecialchars($data['name']) ?></span>
            </div>
            <span style="font-weight:600;font-variant-numeric:tabular-nums;">
              <?= number_format($data['hours'], 1, ',', '') ?> u
              <span class="text-muted-foreground" style="font-weight:400;font-size:0.75rem;">(<?= $data['shifts'] ?>)</span>
            </span>
          </div>
          <?php $grandTotal += $data['hours']; ?>
        <?php endforeach; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0 0;font-size:0.9rem;font-weight:700;border-top:2px solid var(--border);margin-top:0.25rem;">
          <span>Totaal</span>
          <span style="font-variant-numeric:tabular-nums;"><?= number_format($grandTotal, 1, ',', '') ?> u</span>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('toggleAllBtn')?.addEventListener('click', function() {
        var allOpen = true;
        document.querySelectorAll('.day-card').forEach(function(d) { if (!d.open) allOpen = false; });
        document.querySelectorAll('.day-card').forEach(function(d) { d.open = !allOpen; });
        this.textContent = allOpen ? 'Alles openen' : 'Alles sluiten';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
