<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
$displayName = $_SESSION['display_name'] ?? 'Gast';

function isActive($page, $dir = null): string {
    global $currentPage, $currentDir;
    if ($dir && $currentDir === $dir) return 'nav-link--active';
    if ($currentPage === $page) return 'nav-link--active';
    return '';
}

$flashSuccess = flash('success');
$flashError = flash('error');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= APP_NAME ?> | <?= $title ?? 'Dashboard' ?></title>
  <link rel="icon" type="image/svg+xml" href="/public/icon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
  <header class="site-header">
    <div class="inner">
      <a href="/index.php" class="logo">
        <span class="logo-icon">
          <svg width="32" height="32" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 5h3l2.2 12.3a2 2 0 0 0 2 1.7h9.4a2 2 0 0 0 2-1.6L26 9H8.5" />
            <circle cx="13" cy="26" r="1.7" />
            <circle cx="23" cy="26" r="1.7" />
            <path d="M18.5 10.5l-3 4h3l-3 4" stroke="currentColor" stroke-width="1.7" />
          </svg>
        </span>
        <span class="logo-text"><?= APP_NAME ?></span>
      </a>

      <nav class="nav-desktop" id="desktopNav">
        <a href="/index.php" class="nav-link <?= isActive('index.php') ?>">Dashboard</a>
        <a href="/products/index.php" class="nav-link <?= isActive('', 'products') ?>">Producten</a>
        <a href="/categories/index.php" class="nav-link <?= isActive('', 'categories') ?>">Categorieën</a>
        <a href="/suppliers/index.php" class="nav-link <?= isActive('', 'suppliers') ?>">Leveranciers</a>
        <a href="/orders/index.php" class="nav-link <?= isActive('', 'orders') ?>">Bestellingen</a>
        <a href="/personeel/index.php" class="nav-link <?= isActive('', 'personeel') ?>">Personeel</a>
        <a href="/prices/index.php" class="nav-link <?= isActive('', 'prices') ?>">Prijzen</a>
        <a href="/api/index.php" class="nav-link <?= isActive('', 'api') ?>">API Zoeken</a>
      </nav>

      <div class="header-right">
        <div class="header-user">
          <span class="header-user__name"><?= htmlspecialchars($displayName) ?></span>
          <?php if (($_SESSION['username'] ?? '') === 'admin'): ?>
            <a href="/personeel/beheer.php" class="btn btn-ghost" title="Beheer" style="display:inline-flex;align-items:center;padding:0.5rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
            </a>
          <?php endif; ?>
          <a href="/logout.php" class="btn btn-ghost btn-logout">Uitloggen</a>
        </div>
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <nav class="mobile-nav" id="mobileNav">
      <a href="/index.php" class="nav-link <?= isActive('index.php') ?>">Dashboard</a>
      <a href="/products/index.php" class="nav-link <?= isActive('', 'products') ?>">Producten</a>
      <a href="/categories/index.php" class="nav-link <?= isActive('', 'categories') ?>">Categorieën</a>
      <a href="/suppliers/index.php" class="nav-link <?= isActive('', 'suppliers') ?>">Leveranciers</a>
      <a href="/orders/index.php" class="nav-link <?= isActive('', 'orders') ?>">Bestellingen</a>
      <a href="/personeel/index.php" class="nav-link <?= isActive('', 'personeel') ?>">Personeel</a>
      <a href="/prices/index.php" class="nav-link <?= isActive('', 'prices') ?>">Prijzen</a>
      <a href="/api/index.php" class="nav-link <?= isActive('', 'api') ?>">API Zoeken</a>
      <div class="mobile-nav__footer">
        <span class="text-sm text-muted-foreground"><?= htmlspecialchars($displayName) ?></span>
        <a href="/logout.php" class="btn btn-ghost">Uitloggen</a>
      </div>
    </nav>
  </header>

  <?php if ($flashSuccess): ?>
    <div class="flash flash--success"><?= htmlspecialchars($flashSuccess) ?></div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="flash flash--error"><?= htmlspecialchars($flashError) ?></div>
  <?php endif; ?>

  <main class="main-content">
