  </main>

  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-top">
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
        <p class="text-sm text-muted-foreground">Supermarktbeheersysteem &mdash; CRUD, OOP &amp; API</p>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Alle rechten voorbehouden.</p>
      </div>
    </div>
  </footer>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('mobileMenuToggle');
    var nav = document.getElementById('mobileNav');
    if (toggle && nav) {
      toggle.addEventListener('click', function() {
        nav.classList.toggle('mobile-nav--open');
      });
    }
  });
  </script>
</body>
</html>
