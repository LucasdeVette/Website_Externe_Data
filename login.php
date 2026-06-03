<?php
require_once __DIR__ . '/includes/init.php';

use App\Service\AuthService;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthService();
    if ($auth->login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: /index.php');
        exit;
    }
    $error = 'Ongeldige gebruikersnaam of wachtwoord.';
}

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inloggen | <?= APP_NAME ?></title>
  <link rel="icon" type="image/svg+xml" href="/public/icon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/style.css">
</head>
<body class="login-page">
  <div class="login-card">
    <div class="text-center" style="margin-bottom:2rem;">
      <a href="/" class="logo" style="justify-content:center;">
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
      <h1 style="font-size:1.5rem;font-weight:600;margin-top:1.5rem;">Welkom terug</h1>
      <p class="text-muted-foreground">Log in om door te gaan</p>
    </div>

    <?php if ($error): ?>
      <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" style="display:flex;flex-direction:column;gap:1rem;">
      <div class="field">
        <label for="username" class="field__label">Gebruikersnaam</label>
        <input id="username" name="username" type="text" class="field__input" placeholder="admin" required>
      </div>
      <div class="field">
        <label for="password" class="field__label">Wachtwoord</label>
        <input id="password" name="password" type="password" class="field__input" placeholder="wachtwoord" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Inloggen</button>
    </form>

    <p class="text-muted-foreground" style="font-size:0.85rem;margin-top:1.5rem;text-align:center;">
      Demo: <strong>admin</strong> / <strong>password</strong>
    </p>
  </div>
</body>
</html>
