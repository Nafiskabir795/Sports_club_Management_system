/* Theme controller: warm light mode + OLED dark mode */
(function () {
  // Declares a JavaScript variable.
  const key = "sportsClubTheme";

  // Defines a reusable JavaScript function.
  function preferredTheme() {
    // Declares a JavaScript variable.
    const saved = localStorage.getItem(key);
    // Checks a condition before continuing.
    if (saved === "light" || saved === "dark") return saved;
    // Returns a value from the current function.
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  // Executes the current JavaScript statement/block.
  }

  // Defines a reusable JavaScript function.
  function applyTheme(theme) {
    // Executes the current JavaScript statement/block.
    document.documentElement.dataset.theme = theme;
    // Declares a JavaScript variable.
    const btn = document.querySelector(".theme-toggle");
    // Checks a condition before continuing.
    if (btn) {
      // Changes the visible text of an HTML element.
      btn.textContent = theme === "dark" ? "☀" : "☾";
      // Executes the current JavaScript statement/block.
      btn.setAttribute("aria-label", theme === "dark" ? "Switch to light mode" : "Switch to dark mode");
      // Executes the current JavaScript statement/block.
      btn.title = theme === "dark" ? "Light mode" : "Dark mode";
    // Executes the current JavaScript statement/block.
    }
  // Executes the current JavaScript statement/block.
  }

  // Defines a reusable JavaScript function.
  function addToggle() {
    // Checks a condition before continuing.
    if (document.querySelector(".theme-toggle")) return;
    // Declares a JavaScript variable.
    const btn = document.createElement("button");
    // Changes CSS classes on an HTML element.
    btn.className = "theme-toggle";
    // Executes the current JavaScript statement/block.
    btn.type = "button";
    // Executes the current JavaScript statement/block.
    btn.setAttribute("aria-label", "Switch theme");
    // Attaches an event handler.
    btn.addEventListener("click", function () {
      // Declares a JavaScript variable.
      const next = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
      // Executes the current JavaScript statement/block.
      localStorage.setItem(key, next);
      // Executes the current JavaScript statement/block.
      applyTheme(next);
    // Executes the current JavaScript statement/block.
    });
    // Executes the current JavaScript statement/block.
    document.body.appendChild(btn);
    // Executes the current JavaScript statement/block.
    applyTheme(document.documentElement.dataset.theme || preferredTheme());
  // Executes the current JavaScript statement/block.
  }

  // Executes the current JavaScript statement/block.
  applyTheme(preferredTheme());
  // Checks a condition before continuing.
  if (document.readyState === "loading") {
    // Attaches an event handler.
    document.addEventListener("DOMContentLoaded", addToggle);
  // Checks a condition before continuing.
  } else {
    // Executes the current JavaScript statement/block.
    addToggle();
  // Executes the current JavaScript statement/block.
  }
// Executes the current JavaScript statement/block.
})();
