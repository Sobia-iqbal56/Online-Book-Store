// js/main.js — Online Book Store Client-Side Scripts

document.addEventListener('DOMContentLoaded', function () {

  // ── Auto-dismiss alerts after 5 seconds ───────────────
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity .4s ease';
      alert.style.opacity    = '0';
      setTimeout(function () { alert.remove(); }, 400);
    }, 5000);
  });

  // ── Cart quantity: prevent 0 or negative on blur ──────
  const qtyInputs = document.querySelectorAll('input[type="number"][name="qty"]');
  qtyInputs.forEach(function (input) {
    input.addEventListener('change', function () {
      if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
        this.value = 1;
      }
    });
  });

  // ── Search form: trim whitespace before submit ────────
  const searchForm = document.querySelector('.search-form');
  if (searchForm) {
    searchForm.addEventListener('submit', function () {
      const q = this.querySelector('input[name="q"]');
      if (q) q.value = q.value.trim();
    });
  }

});
