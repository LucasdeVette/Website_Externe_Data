<?php
/**
 * Shared shift form partial.
 * Expects:
 *   $shift         - WorkShift|null (null = create mode)
 *   $action        - string (form action value, e.g. 'update' or 'create_shift')
 *   $users         - User[]
 *   $showUserSelect - bool (show dropdown or hidden input)
 *   $selectedUserId - int (for hidden input when !$showUserSelect)
 *   $defaultDate   - string (Y-m-d fallback for create mode)
 *   $cancelUrl     - string|null (null = no cancel button)
 */
$isEdit = $shift !== null;
?>
<form method="POST" class="form-stack">
  <?= csrfField() ?>
  <input type="hidden" name="action" value="<?= $action ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= $shift->getId() ?>">
  <?php endif; ?>

  <?php if ($showUserSelect): ?>
    <div class="field">
      <label class="field__label" for="shift_user_id">Medewerker</label>
      <select id="shift_user_id" name="user_id" class="field__input" required>
        <?php foreach ($users as $u): ?>
          <option value="<?= $u->getId() ?>" <?= $isEdit && $shift->getUserId() === $u->getId() ? 'selected' : '' ?>>
            <?= htmlspecialchars($u->getDisplayName()) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php else: ?>
    <input type="hidden" name="user_id" value="<?= $selectedUserId ?>">
  <?php endif; ?>

  <div class="grid" style="grid-template-columns:1fr 1fr 1fr 2fr;gap:0.75rem;align-items:end;">
    <div class="field">
      <label class="field__label" for="shift_date">Datum</label>
      <input id="shift_date" name="shift_date" type="date" class="field__input"
        value="<?= $isEdit ? $shift->getShiftDate() : $defaultDate ?>" required>
    </div>
    <div class="field">
      <label class="field__label" for="shift_start_time">Begin</label>
      <input id="shift_start_time" name="start_time" type="text" class="field__input" placeholder="HH:MM"
        pattern="[0-9]{2}:[0-9]{2}" maxlength="5" inputmode="numeric"
        value="<?= $isEdit ? substr($shift->getStartTime(), 0, 5) : '' ?>" required>
    </div>
    <div class="field">
      <label class="field__label" for="shift_end_time">Eind</label>
      <input id="shift_end_time" name="end_time" type="text" class="field__input" placeholder="HH:MM"
        pattern="[0-9]{2}:[0-9]{2}" maxlength="5" inputmode="numeric"
        value="<?= $isEdit ? substr($shift->getEndTime(), 0, 5) : '' ?>" required>
    </div>
    <div class="field">
      <label class="field__label" for="shift_notes">Notities</label>
      <input id="shift_notes" name="notes" type="text" class="field__input" placeholder="Optioneel"
        value="<?= $isEdit ? ($shift->getNotes() ?? '') : '' ?>">
    </div>
  </div>

  <div class="flex gap-2">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Opslaan' : 'Toevoegen' ?></button>
    <?php if ($cancelUrl): ?>
      <a href="<?= $cancelUrl ?>" class="btn btn-ghost">Annuleren</a>
    <?php endif; ?>
  </div>
</form>
