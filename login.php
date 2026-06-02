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
    <div class="text-center mb-8">
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
      <h1 class="login-title" style="margin-top:1.5rem;">Welkom terug</h1>
      <p class="login-desc">Log in om door te gaan</p>
    </div>

    <?php if ($error): ?>
      <div class="login-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <div class="form-group">
        <label for="username" class="form-label">Gebruikersnaam</label>
        <input id="username" name="username" type="text" class="form-input" placeholder="admin" required>
      </div>
      <div class="form-group">
        <label for="password" class="form-label">Wachtwoord</label>
        <input id="password" name="password" type="password" class="form-input" placeholder="wachtwoord" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-submit">Inloggen</button>
    </form>

    <p class="form-footer" style="margin-top:1.5rem;">
      Demo: <strong>admin</strong> / <strong>password</strong>
    </p>
  </div>
</body>
</html>
