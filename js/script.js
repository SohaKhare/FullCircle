// ─── DARK MODE ───────────────────────────────────────────────
function toggleDarkMode() {
  var isDark = document.body.classList.toggle('dark-mode');
  localStorage.setItem('darkMode', isDark ? '1' : '0');
}
(function() {
  if (localStorage.getItem('darkMode') === '1') {
    document.body.classList.add('dark-mode');
  }
})();

// ─── MODAL ───────────────────────────────────────────────────
function openModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('active');
}
function closeModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('active');
}
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
  }
});

// ─── CONFIRM DELETE ──────────────────────────────────────────
function confirmDelete(msg) {
  return confirm(msg || 'Are you sure you want to delete this?');
}

// ─── AUTO-DISMISS ALERTS ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(el) {
      el.style.transition = 'opacity 0.5s';
      el.style.opacity = '0';
      setTimeout(function() { el.remove(); }, 500);
    });
  }, 5000);
});

// ─── NGO FIELD TOGGLE ────────────────────────────────────────
function toggleNGOField() {
  var role  = document.getElementById('role');
  var field = document.getElementById('ngo-field');
  if (!role || !field) return;
  var isNGO = role.value === 'ngo';
  field.style.display = isNGO ? 'block' : 'none';
  var inp = field.querySelector('input');
  if (inp) inp.required = isNGO;
}

// ─── COUNTER ANIMATION ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      if (!e.isIntersecting) return;
      var el = e.target;
      var target = parseInt(el.dataset.count, 10) || 0;
      var startTime = null;
      function step(ts) {
        if (!startTime) startTime = ts;
        var p = Math.min((ts - startTime) / 1400, 1);
        el.textContent = Math.floor(p * target).toLocaleString();
        if (p < 1) requestAnimationFrame(step);
        else el.textContent = target.toLocaleString();
      }
      requestAnimationFrame(step);
      observer.unobserve(el);
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.stat-num[data-count]').forEach(function(el) {
    observer.observe(el);
  });
});

// ─── REGISTRATION VALIDATION ──────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('register-form');
  if (!form) return;

  var emailInput   = document.getElementById('reg-email');
  var phoneInput   = document.getElementById('reg-phone');
  var pwInput      = document.getElementById('reg-password');
  var confirmInput = document.getElementById('reg-confirm');

  function setHint(el, msg, type) {
    if (!el) return;
    el.textContent = msg;
    el.className = 'field-hint' + (msg ? ' show ' + type : '');
  }
  function addClass(input, cls) {
    if (!input) return;
    input.classList.remove('is-valid', 'is-invalid');
    if (cls) input.classList.add(cls);
  }

  // Email
  function validateEmail() {
    if (!emailInput) return true;
    var val = emailInput.value.trim();
    var hint = document.getElementById('hint-email');
    if (!val) { setHint(hint,'',''); addClass(emailInput,''); return false; }
    var ok = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val);
    setHint(hint, ok ? 'Looks good.' : 'Please enter a valid email address.', ok ? 'success' : 'error');
    addClass(emailInput, ok ? 'is-valid' : 'is-invalid');
    return ok;
  }
  if (emailInput) emailInput.addEventListener('input', validateEmail);

  // Phone
  function validatePhone() {
    if (!phoneInput) return true;
    var raw = phoneInput.value.trim();
    var hint = document.getElementById('hint-phone');
    if (!raw) { setHint(hint,'',''); addClass(phoneInput,''); return true; }
    var digits = raw.replace(/[\s\-\(\)\+]/g,'');
    var ok = /^\d{10,13}$/.test(digits);
    setHint(hint, ok ? 'Valid phone number.' : 'Must be 10 to 13 digits.', ok ? 'success' : 'error');
    addClass(phoneInput, ok ? 'is-valid' : 'is-invalid');
    return ok;
  }
  if (phoneInput) phoneInput.addEventListener('input', validatePhone);

  // Password strength
  function scorePassword(pw) {
    var s = 0;
    if (pw.length >= 8) s++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++;
    if (/\d/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw)) s++;
    return Math.min(s, 4);
  }
  var strengthLabels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

  function validatePassword() {
    if (!pwInput) return true;
    var val  = pwInput.value;
    var hint  = document.getElementById('hint-password');
    var meter = document.getElementById('pw-strength-meter');
    var lbl   = document.getElementById('pw-strength-label');
    if (!val) {
      setHint(hint,'',''); addClass(pwInput,'');
      if (meter) meter.className = 'pw-strength';
      return false;
    }
    if (meter) {
      var score = scorePassword(val);
      meter.className = 'pw-strength show strength-' + score;
      if (lbl) lbl.textContent = strengthLabels[score];
    }
    if (val.length < 6) {
      setHint(hint,'Must be at least 6 characters.','error');
      addClass(pwInput,'is-invalid'); return false;
    }
    setHint(hint,'',''); addClass(pwInput,'is-valid');
    if (confirmInput && confirmInput.value) validateConfirm();
    return true;
  }
  if (pwInput) pwInput.addEventListener('input', validatePassword);

  // Confirm password
  function validateConfirm() {
    if (!confirmInput) return true;
    var val  = confirmInput.value;
    var hint = document.getElementById('hint-confirm');
    var pw   = pwInput ? pwInput.value : '';
    if (!val) { setHint(hint,'',''); addClass(confirmInput,''); return false; }
    var ok = val === pw;
    setHint(hint, ok ? 'Passwords match.' : 'Passwords do not match.', ok ? 'success' : 'error');
    addClass(confirmInput, ok ? 'is-valid' : 'is-invalid');
    return ok;
  }
  if (confirmInput) confirmInput.addEventListener('input', validateConfirm);

  // Submit guard
  form.addEventListener('submit', function(e) {
    var ok = true;
    if (!validateEmail()) ok = false;
    if (phoneInput && phoneInput.value.trim() && !validatePhone()) ok = false;
    if (!validatePassword()) ok = false;
    if (!validateConfirm()) ok = false;
    if (!ok) e.preventDefault();
  });
});
