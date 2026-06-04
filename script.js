// Theme toggle
document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("theme-toggle");
  if (themeToggle) {
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
      document.documentElement.classList.add("dark");
    }

    themeToggle.addEventListener("click", () => {
      document.documentElement.classList.toggle("dark");
      const isDark = document.documentElement.classList.contains("dark");
      localStorage.setItem("theme", isDark ? "dark" : "light");
    });
  }

  // Password toggle
  document.querySelectorAll(".toggle-password").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = btn.closest(".input-wrapper").querySelector("input");
      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      btn.querySelector(".eye-open")?.classList.toggle("hidden", isPassword);
      btn.querySelector(".eye-closed")?.classList.toggle("hidden", !isPassword);
    });
  });

  // Login form submission
  document.querySelectorAll(".login-form").forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const btn = form.querySelector(".btn-submit");
      const originalText = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = "Signing in...";
      await new Promise((resolve) => setTimeout(resolve, 1000));
      window.location.href = "/";
    });
  });
});
