document.addEventListener('DOMContentLoaded', function () {
  // Show / hide password
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.togglePassword);
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      btn.classList.toggle('on', input.type === 'text');
    });
  });

  // Button spinner on submit
  document.querySelectorAll('form[data-loading]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var b = form.querySelector('button[type=submit]');
      if (b) { setTimeout(function () { b.classList.add('loading'); }, 0); }
    });
  });

  // Confirm dialogs
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
    });
  });

  // Demo accounts: click to fill the login form
  document.querySelectorAll('[data-demo]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var email = document.getElementById('email'), pass = document.getElementById('password');
      if (email) email.value = btn.dataset.demo;
      if (pass) pass.value = btn.dataset.pass || 'password';
    });
  });

  // Tabs (project page)
  var tabs = document.querySelectorAll('[data-tab]');
  function showTab(name) {
    tabs.forEach(function (t) { t.classList.toggle('active', t.dataset.tab === name); });
    document.querySelectorAll('.tab-panel').forEach(function (p) { p.hidden = p.id !== 'tab-' + name; });
  }
  if (tabs.length) {
    tabs.forEach(function (t) { t.addEventListener('click', function () { showTab(t.dataset.tab); history.replaceState(null, '', '#' + t.dataset.tab); }); });
    var start = location.hash.replace('#', '');
    showTab(document.getElementById('tab-' + start) ? start : tabs[0].dataset.tab);
  }

  // Mobile sidebar
  var menu = document.getElementById('menu-btn');
  if (menu) {
    menu.addEventListener('click', function () { document.body.classList.toggle('nav-open'); });
    document.addEventListener('click', function (e) {
      if (document.body.classList.contains('nav-open') && !e.target.closest('.sidebar') && !e.target.closest('#menu-btn')) document.body.classList.remove('nav-open');
    });
  }
});

/* ---- Motion helpers ---- */
document.addEventListener('DOMContentLoaded', function () {
  var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (calm) return;

  // Count-up for dashboard numbers (e.g. 12, 68%)
  document.querySelectorAll('.stat .num').forEach(function (el) {
    var m = el.textContent.trim().match(/^(\d+)(%?)$/);
    if (!m) return;
    var target = parseInt(m[1], 10), suffix = m[2], start = null, dur = 900;
    if (target === 0) return;
    el.textContent = '0' + suffix;
    function tick(ts) {
      if (start === null) start = ts;
      var p = Math.min((ts - start) / dur, 1), eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased) + suffix;
      if (p < 1) requestAnimationFrame(tick);
    }
    setTimeout(function () { requestAnimationFrame(tick); }, 200);
  });

  // Fade out before navigating to another page of the app
  document.querySelectorAll('a[href]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
      if (a.host !== window.location.host || a.hasAttribute('download') || /\/download$/.test(a.pathname)) return;
      document.body.classList.add('leaving');
    });
  });
  window.addEventListener('pageshow', function () { document.body.classList.remove('leaving'); });
});

/* Background: soft color glows (no bubbles). */
document.addEventListener('DOMContentLoaded', function () {
  ['a', 'b', 'c'].forEach(function (k) {
    var g = document.createElement('div'); g.className = 'glow ' + k; g.setAttribute('aria-hidden', 'true'); document.body.prepend(g);
  });
});

/* Register page: sample ID buttons and role auto-pick from the ID prefix */
document.addEventListener('DOMContentLoaded', function () {
  var idInput = document.getElementById('id_number'), role = document.getElementById('role');
  if (!idInput) return;
  function pick() {
    if (!role) return;
    var v = idInput.value.toUpperCase();
    if (v.indexOf('TE-') === 0) role.value = 'instructor';
    else if (v.indexOf('AD-') === 0) role.value = 'adviser';
    else if (v.indexOf('CO-') === 0) role.value = 'coordinator';
    else if (v.indexOf('ST-') === 0 && ['instructor', 'adviser', 'coordinator'].indexOf(role.value) !== -1) role.value = 'student';
  }
  idInput.addEventListener('input', pick);
  document.querySelectorAll('[data-fill-id]').forEach(function (b) {
    b.addEventListener('click', function () { idInput.value = b.dataset.fillId; pick(); idInput.focus(); });
  });
});

/* Light beams that run along the grid lines (grid cell = 46px) */
document.addEventListener('DOMContentLoaded', function () {
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var CELL = 46, wrap = document.createElement('div');
  wrap.className = 'beams'; wrap.setAttribute('aria-hidden', 'true');
  var small = window.innerWidth < 860, rows = Math.floor(window.innerHeight * 0.75 / CELL), cols = Math.floor(window.innerWidth / CELL);

  function add(cls, pos, dur, delay) {
    var b = document.createElement('div');
    b.className = 'beam ' + cls;
    if (cls === 'h') b.style.top = pos + 'px'; else b.style.left = pos + 'px';
    b.style.animationDuration = dur + 's';
    b.style.animationDelay = delay + 's';
    wrap.appendChild(b);
  }
  function rnd(a, b) { return a + Math.random() * (b - a); }

  var h = small ? 4 : 8, v = small ? 3 : 7;
  for (var i = 0; i < h; i++) add('h', (1 + Math.floor(Math.random() * Math.max(rows, 2))) * CELL, rnd(8, 16), -rnd(0, 14));
  for (var j = 0; j < v; j++) add('v', (1 + Math.floor(Math.random() * Math.max(cols, 2))) * CELL, rnd(9, 18), -rnd(0, 16));
  document.body.prepend(wrap);
});