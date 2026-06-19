<?php

function semaine(int $week, int $year): array
{
    if ($week < 1)  { $week = 53; $year--; }
    if ($week > 53) { $week = 1;  $year++; }
    return [$week, $year];
}

function employee_colors(): array
{
    return ['#2563eb','#dc2626','#16a34a','#d97706','#7c3aed','#db2777','#0891b2','#ca8a04','#4f46e5','#c026d3','#059669','#ea580c'];
}

function build_user_color_map(array $users): array
{
    $colors = employee_colors();
    $map = [];
    $i = 0;
    foreach ($users as $u) {
        $map[$u->getId()] = $colors[$i % count($colors)];
        $i++;
    }
    return $map;
}

function render_errors(array $errors): void
{
    if (empty($errors)) return;
    echo '<div class="alert alert--error"><ul style="margin:0;padding-left:1.25rem;">';
    foreach ($errors as $e) {
        echo '<li>' . htmlspecialchars($e) . '</li>';
    }
    echo '</ul></div>';
}

function icon_edit(int $size = 13): string
{
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
}

function icon_delete(int $size = 13): string
{
    return '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
}

function colored_dot(string $color, int $size = 8): string
{
    return '<span style="width:'.$size.'px;height:'.$size.'px;border-radius:50%;flex-shrink:0;background:'.$color.';"></span>';
}

function delete_shift_form(int $id, int $iconSize = 13): string
{
    return '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Dienst verwijderen?\');">'
        . csrfField()
        . '<input type="hidden" name="action" value="delete_shift">'
        . '<input type="hidden" name="id" value="'.$id.'">'
        . '<button type="submit" class="btn-icon btn-icon--danger" title="Verwijderen">'
        . icon_delete($iconSize)
        . '</button></form>';
}
