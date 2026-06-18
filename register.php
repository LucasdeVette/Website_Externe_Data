<?php
require_once __DIR__ . '/includes/init.php';

use App\Service\AuthService;

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $displayName = $_POST["display_name"] ?? "";
    $username = $_POST["username"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    $passwordRepeat = $_POST["password_repeat"] ?? "";

    if ($displayName == "" || $username == "" || $password == "" || $passwordRepeat == "") {
        $message = "Vul alle verplichte velden in.";
    } elseif ($password !== $passwordRepeat) {
        $message = "De wachtwoorden komen niet overeen.";
    } else {
        $auth = new AuthService();
        $auth->register($username, $password, $displayName, $email);

        header("Location: /");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registreren | <?= APP_NAME ?></title>
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
        <span class="logo-icon">🛒</span>
        <span class="logo-text"><?= APP_NAME ?></span>
      </a>
      <h1 style="font-size:1.5rem;font-weight:600;margin-top:1.5rem;">Account registreren</h1>
      <p class="text-muted-foreground">Maak een nieuw account aan</p>
    </div>

    <?php if ($message): ?>
      <div class="login-error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" style="display:flex;flex-direction:column;gap:1rem;">
      <div class="field">
        <label for="display_name" class="field__label">Naam</label>
        <input id="display_name" name="display_name" type="text" class="field__input" placeholder="Naam" required>
      </div>

      <div class="field">
        <label for="username" class="field__label">Gebruikersnaam</label>
        <input id="username" name="username" type="text" class="field__input" placeholder="gebruikersnaam" required>
      </div>

      <div class="field">
        <label for="email" class="field__label">Email</label>
        <input id="email" name="email" type="email" class="field__input" placeholder="email">
      </div>

      <div class="field">
        <label for="password" class="field__label">Wachtwoord</label>
        <input id="password" name="password" type="password" class="field__input" placeholder="wachtwoord" required>
      </div>

      <div class="field">
        <label for="password_repeat" class="field__label">Herhaal wachtwoord</label>
        <input id="password_repeat" name="password_repeat" type="password" class="field__input" placeholder="herhaal wachtwoord" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Registreren</button>
    </form>

    <p class="text-muted-foreground" style="font-size:0.85rem;margin-top:1.5rem;text-align:center;">
      Al een account? <a href="/">Log hier in</a>
    </p>
  </div>
</body>
</html>