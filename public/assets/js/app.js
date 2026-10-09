(function () {
  'use strict';

  // Flash messages from the server -> SweetAlert2 toasts
  var types = { success: 'success', danger: 'error', error: 'error', warning: 'warning', info: 'info' };
  (window.DE_FLASH || []).forEach(function (f) {
    Swal.fire({
      toast: true, position: 'top-end', timer: 4500, timerProgressBar: true, showConfirmButton: false,
      icon: types[f.type] || 'info', title: f.message
    });
  });

  // <form data-confirm="Cancel this booking?"> asks before submitting
  document.addEventListener('submit', function (ev) {
    var form = ev.target, msg = form.getAttribute && form.getAttribute('data-confirm');
    if (!msg || form.dataset.confirmed) return;
    ev.preventDefault();
    Swal.fire({ title: 'Are you sure?', text: msg, icon: 'question', showCancelButton: true,
      confirmButtonText: 'Yes, continue', confirmButtonColor: '#0f3d5e' })
      .then(function (r) { if (r.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); } });
  });

  // Keep return date >= pickup date on any paired date inputs
  document.querySelectorAll('input[type="date"][data-date-start]').forEach(function (start) {
    var end = document.querySelector(start.getAttribute('data-date-start'));
    if (!end) return;
    var sync = function () { if (start.value) { end.min = start.value; if (end.value && end.value < start.value) end.value = start.value; } };
    start.addEventListener('change', sync); sync();
  });

  // Disable submit buttons after click to prevent double submits
  document.querySelectorAll('form[data-once]').forEach(function (f) {
    f.addEventListener('submit', function () {
      var b = f.querySelector('button[type="submit"]');
      if (b) { setTimeout(function () { b.disabled = true; }, 0); }
    });
  });

  // Password show/hide
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.querySelector(btn.getAttribute('data-toggle-password'));
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('i').className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
  });
})();
