// ============================================================
// Finance App - Main JavaScript
// ============================================================

'use strict';

// === CONFIG ===
const API_BASE = 'backend/';

// === STATE ===
const state = {
  user: null,
  currentPage: 'dashboard',
  theme: localStorage.getItem('theme') || 'dark',
  charts: {},
  transactions: { data: [], total: 0, page: 1, limit: 15 },
  editTxId: null,
  selectedType: 'expense',
  donutType: 'expense',
  currentYear: new Date().getFullYear(),
};

function uiIcon(name) {
  const icons = {
    sun: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    moon: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4 7 7 0 1 0 20 15.5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
  };
  return icons[name] || '';
}

function categorySvg(icon) {
  const paths = {
    briefcase: '<rect x="3" y="7" width="18" height="13" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" fill="none" stroke="currentColor" stroke-width="2"/>',
    laptop: '<path d="M4 5h16v11H4z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 19h20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    'trending-up': '<path d="M4 17l6-6 4 4 6-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 7h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    coffee: '<path d="M4 8h11v6a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M15 10h2a2 2 0 0 1 0 4h-2M6 3v2M10 3v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    truck: '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="7" cy="18" r="2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="18" r="2" fill="none" stroke="currentColor" stroke-width="2"/>',
    'shopping-cart': '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l3 13h10l3-9H7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
    'file-text': '<path d="M6 3h8l4 4v14H6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 3v5h5M9 13h6M9 17h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
    heart: '<path d="M20.8 8.6c0 5.2-8.8 10.4-8.8 10.4S3.2 13.8 3.2 8.6A4.6 4.6 0 0 1 12 6a4.6 4.6 0 0 1 8.8 2.6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>',
    film: '<rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 5v14M16 5v14M3 10h5M16 10h5M3 14h5M16 14h5" fill="none" stroke="currentColor" stroke-width="2"/>',
    book: '<path d="M4 5a3 3 0 0 1 3-3h13v18H7a3 3 0 0 0-3 3z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 5v18" fill="none" stroke="currentColor" stroke-width="2"/>',
    save: '<path d="M5 4h12l2 2v14H5z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M8 4v6h8V4M8 20v-6h8v6" fill="none" stroke="currentColor" stroke-width="2"/>',
    tag: '<path d="M20 13l-7 7L4 11V4h7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.5"/>',
  };
  const body = paths[icon] || paths.tag;
  return `<svg viewBox="0 0 24 24" aria-hidden="true">${body}</svg>`;
}
function actionIcon(name) {
  const icons = {
    edit: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l11-11a2.8 2.8 0 0 0-4-4L4 16v4z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M13.5 6.5l4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    trash: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 11v6M14 11v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M6 7l1 14h10l1-14M9 7V4h6v3" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg>',
    check: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    search: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    empty: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/></svg>',
    download: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
  };
  return icons[name] || '';
}
// ============================================================
// UTILS
// ============================================================
const formatRupiah = (n) => {
  return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
};

const formatDate = (d) => {
  if (!d) return '-';
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const sanitize = (str) => {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
};

// ============================================================
// API HELPER
// ============================================================
async function api(endpoint, options = {}) {
  try {
    const res = await fetch(API_BASE + endpoint, {
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      ...options,
    });
    const data = await res.json();
    return data;
  } catch (err) {
    console.error('API Error:', err);
    return { success: false, message: 'Koneksi gagal. Periksa server.' };
  }
}

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  const icon = type === 'success' ? actionIcon('check') : actionIcon('close');
  toast.innerHTML = `
    <span class="toast-icon">${icon}</span>
    <span class="toast-msg">${sanitize(message)}</span>
    <button class="toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">${actionIcon('close')}</button>
  `;
  container.appendChild(toast);
  setTimeout(() => toast.style.opacity = '0', 3200);
  setTimeout(() => toast.remove(), 3500);
}

// ============================================================
// LOADING
// ============================================================
function setLoading(show) {
  document.getElementById('loading-overlay').classList.toggle('show', show);
}

// ============================================================
// THEME
// ============================================================
function applyTheme(theme) {
  state.theme = theme;
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  const btn = document.getElementById('theme-toggle');
  if (btn) btn.innerHTML = theme === 'dark' ? uiIcon('sun') : uiIcon('moon');
}

function toggleTheme() {
  applyTheme(state.theme === 'dark' ? 'light' : 'dark');
  // Recreate charts with new theme
  setTimeout(() => {
    if (state.currentPage === 'dashboard') loadDashboard();
  }, 100);
}

// ============================================================
// AUTH
// ============================================================
async function checkAuth() {
  const res = await api('auth.php?action=check');
  if (res.success) {
    state.user = res.data;
    showApp();
  } else {
    showAuth('login');
  }
}

function showAuth(mode) {
  document.getElementById('auth-section').style.display = 'flex';
  document.getElementById('app-section').style.display = 'none';
  if (mode === 'register') {
    document.getElementById('login-form-wrap').style.display = 'none';
    document.getElementById('register-form-wrap').style.display = 'block';
  } else {
    document.getElementById('login-form-wrap').style.display = 'block';
    document.getElementById('register-form-wrap').style.display = 'none';
  }
}

function showApp() {
  document.getElementById('auth-section').style.display = 'none';
  document.getElementById('app-section').style.display = 'flex';
  updateUserUI();
  navigateTo('dashboard');
}

function updateUserUI() {
  if (!state.user) return;
  const name  = state.user.name  || 'User';
  const email = state.user.email || '';
  const initial = name.charAt(0).toUpperCase();
  document.querySelectorAll('.user-name').forEach(el => el.textContent = name);
  document.querySelectorAll('.user-email').forEach(el => el.textContent = email);
  document.querySelectorAll('.user-avatar').forEach(el => el.textContent = initial);
  const topbarUser = document.getElementById('topbar-user');
  if (topbarUser) topbarUser.textContent = name;
  const topbarActions = document.querySelector('.topbar-actions');
  if (topbarActions) topbarActions.style.display = 'flex';
}

async function handleLogin(e) {
  e.preventDefault();
  const email    = document.getElementById('login-email').value.trim();
  const password = document.getElementById('login-password').value;
  if (!email || !password) return showToast('Isi semua field!', 'error');

  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Memproses...';

  const firebaseAuth = await waitForFirebaseAuth();
  const res = firebaseAuth
    ? await firebaseAuth.loginWithEmail(email, password)
    : { success: false, message: 'Firebase belum siap. Cek koneksi internet atau refresh halaman.' };

  btn.disabled = false; btn.textContent = 'Masuk';
  if (res.success) {
    state.user = res.data;
    showToast(res.message);
    showApp();
  } else {
    showToast(res.message, 'error');
  }
}

async function handleRegister(e) {
  e.preventDefault();
  const name     = document.getElementById('reg-name').value.trim();
  const email    = document.getElementById('reg-email').value.trim();
  const password = document.getElementById('reg-password').value;
  const confirm  = document.getElementById('reg-confirm').value;

  if (!name || !email || !password) return showToast('Isi semua field!', 'error');
  if (password !== confirm) return showToast('Password tidak cocok!', 'error');
  if (password.length < 6) return showToast('Password minimal 6 karakter!', 'error');

  const btn = e.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Mendaftar...';

  const firebaseAuth = await waitForFirebaseAuth();
  const res = firebaseAuth
    ? await firebaseAuth.registerWithEmail(name, email, password)
    : { success: false, message: 'Firebase belum siap. Cek koneksi internet atau refresh halaman.' };

  btn.disabled = false; btn.textContent = 'Daftar Sekarang';
  if (res.success) {
    state.user = res.data;
    showToast(res.message);
    showApp();
  } else {
    showToast(res.message, 'error');
  }
}

function waitForFirebaseAuth(timeout = 7000) {
  if (window.financeFirebaseAuth) return Promise.resolve(window.financeFirebaseAuth);

  return new Promise((resolve) => {
    const timer = setTimeout(() => {
      window.removeEventListener('financeFirebaseAuthReady', onReady);
      resolve(null);
    }, timeout);

    function onReady() {
      clearTimeout(timer);
      resolve(window.financeFirebaseAuth || null);
    }

    window.addEventListener('financeFirebaseAuthReady', onReady, { once: true });
  });
}

async function handleGoogleLogin() {
  const firebaseAuth = await waitForFirebaseAuth();
  if (!firebaseAuth) return showToast('Firebase belum siap. Cek koneksi internet/browser.', 'error');

  setLoading(true);
  const res = await firebaseAuth.loginWithGoogle();
  setLoading(false);

  if (res.success) {
    state.user = res.data;
    showToast(res.message);
    showApp();
  } else {
    showToast(res.message, 'error');
  }
}

async function handleLogout() {
  try {
    if (window.financeFirebaseAuth) await window.financeFirebaseAuth.signOut();
  } catch (_) {}
  await api('auth.php?action=logout');
  state.user = null;
  Object.values(state.charts).forEach(c => { try { c.destroy(); } catch(_){} });
  state.charts = {};
  showAuth('login');
  showToast('Berhasil logout!');
}

// ============================================================
// NAVIGATION
// ============================================================
function navigateTo(page) {
  state.currentPage = page;

  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

  const pageEl = document.getElementById(`page-${page}`);
  if (pageEl) pageEl.classList.add('active');

  const navEl = document.querySelector(`[data-page="${page}"]`);
  if (navEl) navEl.classList.add('active');

  const titles = {
    dashboard: 'Dashboard',
    add: 'Tambah Transaksi',
    transactions: 'Daftar Transaksi',
    reports: 'Laporan',
    chatbot: 'AI Keuangan',
  };
  document.getElementById('topbar-page').textContent = titles[page] || page;

  // Close mobile sidebar
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('show');

  updateUserUI(); // Ensure user UI is updated on navigation

  // Load data
  switch (page) {
    case 'dashboard':    loadDashboard();     break;
    case 'add':          loadAddForm();       break;
    case 'transactions': loadTransactions();  break;
    case 'reports':      loadReports();       break;
    case 'chatbot':      loadChatbot();       break;
  }
}

// ============================================================
// DASHBOARD
// ============================================================
async function loadDashboard() {
  updateUserUI(); // Ensure UI is updated
  const year = state.currentYear;

  // Summary
  const sumRes = await api(`dashboard.php?action=summary&year=${year}`);
  if (sumRes.success) {
    const d = sumRes.data;
    document.getElementById('stat-income').textContent   = formatRupiah(d.total_income);
    document.getElementById('stat-expense').textContent  = formatRupiah(d.total_expense);
    document.getElementById('stat-balance').textContent  = formatRupiah(d.balance);
    document.getElementById('stat-count').textContent    = d.total_transactions + ' Transaksi';
    document.getElementById('balance-sub').textContent   = d.balance >= 0 ? 'Keuangan sehat' : 'Pengeluaran melebihi pemasukan';
    document.getElementById('balance-sub').style.color   = d.balance >= 0 ? 'var(--accent)' : 'var(--red)';
  }

  // Monthly chart
  const mRes = await api(`dashboard.php?action=monthly_chart&year=${year}`);
  if (mRes.success) renderMonthlyChart(mRes.data);

  // Category chart
  const cRes = await api(`dashboard.php?action=category_chart&year=${year}&type=${state.donutType}`);
  if (cRes.success) renderDonutChart(cRes.data);

  // Recent
  const rRes = await api('dashboard.php?action=recent&limit=7');
  if (rRes.success) renderRecentTransactions(rRes.data);
}

function renderMonthlyChart(data) {
  const ctx = document.getElementById('monthly-chart').getContext('2d');
  if (state.charts.monthly) state.charts.monthly.destroy();

  const isDark = state.theme === 'dark';
  const gridColor = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#8892B0' : '#4A5568';

  state.charts.monthly = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.labels,
      datasets: [
        {
          label: 'Pemasukan',
          data: data.income,
          borderColor: '#10F0A0',
          backgroundColor: 'rgba(16,240,160,0.08)',
          borderWidth: 2.5,
          pointBackgroundColor: '#10F0A0',
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.4,
        },
        {
          label: 'Pengeluaran',
          data: data.expense,
          borderColor: '#FF4757',
          backgroundColor: 'rgba(255,71,87,0.08)',
          borderWidth: 2.5,
          pointBackgroundColor: '#FF4757',
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: { color: textColor, font: { family: 'DM Sans', size: 12 }, usePointStyle: true, pointStyleWidth: 8 }
        },
        tooltip: {
          callbacks: {
            label: (ctx) => ` ${ctx.dataset.label}: ${formatRupiah(ctx.parsed.y)}`
          },
          backgroundColor: isDark ? '#1A2847' : '#fff',
          borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.1)',
          borderWidth: 1,
          titleColor: isDark ? '#F0F4FF' : '#1A2847',
          bodyColor: textColor,
        }
      },
      scales: {
        x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
        y: {
          grid: { color: gridColor },
          ticks: {
            color: textColor, font: { size: 11 },
            callback: (v) => 'Rp ' + (v >= 1e6 ? (v/1e6).toFixed(1)+'jt' : v >= 1e3 ? (v/1e3).toFixed(0)+'rb' : v)
          }
        }
      }
    }
  });
}

function renderDonutChart(data) {
  const ctx = document.getElementById('donut-chart').getContext('2d');
  if (state.charts.donut) state.charts.donut.destroy();

  const isDark = state.theme === 'dark';

  if (!data.labels.length) {
    document.getElementById('donut-legend').innerHTML = '<p style="color:var(--text-500);font-size:13px;text-align:center">Belum ada data</p>';
    return;
  }

  state.charts.donut = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.labels,
      datasets: [{
        data: data.values,
        backgroundColor: data.colors,
        borderColor: isDark ? '#0E1629' : '#fff',
        borderWidth: 3,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => ` ${ctx.label}: ${formatRupiah(ctx.parsed)}`
          },
          backgroundColor: isDark ? '#1A2847' : '#fff',
          borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.1)',
          borderWidth: 1,
          titleColor: isDark ? '#F0F4FF' : '#1A2847',
          bodyColor: isDark ? '#8892B0' : '#4A5568',
        }
      }
    }
  });

  // Legend
  const total = data.values.reduce((a, b) => a + b, 0);
  document.getElementById('donut-legend').innerHTML = data.labels.map((label, i) => `
    <div class="legend-item">
      <div class="legend-dot" style="background:${data.colors[i]}"></div>
      <span>${sanitize(label)}</span>
      <span class="mono" style="margin-left:4px;color:var(--text-300)">${total ? Math.round(data.values[i]/total*100) : 0}%</span>
    </div>
  `).join('');
}

function renderRecentTransactions(txs) {
  const el = document.getElementById('recent-list');
  if (!txs.length) {
    el.innerHTML = `<div class="empty-state"><div class="empty-icon">${actionIcon('empty')}</div><div class="empty-title">Belum ada transaksi</div><div class="empty-desc">Tambahkan transaksi pertama Anda</div></div>`;
    return;
  }
  el.innerHTML = txs.map(tx => `
    <div class="tx-item">
      <div class="tx-icon" style="background:${tx.category_color}22; color:${tx.category_color}">
        ${getCategoryIcon(tx.category_icon)}
      </div>
      <div class="tx-info">
        <div class="tx-desc">${sanitize(tx.description)}</div>
        <div class="tx-meta">${sanitize(tx.category_name)} - ${formatDate(tx.transaction_date)}</div>
      </div>
      <div class="tx-amount ${tx.type}">
        ${tx.type === 'income' ? '+' : '-'}${formatRupiah(tx.amount)}
      </div>
    </div>
  `).join('');
}

// ============================================================
// ADD TRANSACTION
// ============================================================
async function loadAddForm(editData = null) {
  // Reset form
  if (!editData) {
    document.getElementById('tx-form').reset();
    state.editTxId = null;
    document.getElementById('modal-tx-title').textContent = 'Tambah Transaksi';
    document.getElementById('tx-image-path').value = '';
    document.getElementById('tx-date').value = new Date().toISOString().split('T')[0];
    setSelectedType('expense');
  } else {
    state.editTxId = editData.id;
    document.getElementById('modal-tx-title').textContent = 'Edit Transaksi';
    document.getElementById('tx-amount').value = editData.amount;
    document.getElementById('tx-desc').value = editData.description;
    document.getElementById('tx-date').value = editData.transaction_date;
    document.getElementById('tx-image-path').value = editData.image_path || '';
    setSelectedType(editData.type);
    // Load categories then set value
    await loadCategoriesSelect(editData.type, editData.category_id);
    return;
  }

  await loadCategoriesSelect(state.selectedType);
}

async function loadCategoriesSelect(type, selectedId = null) {
  const res = await api(`categories.php?type=${type}`);
  const select = document.getElementById('tx-category');
  select.innerHTML = '<option value="">-- Pilih Kategori --</option>';
  if (res.success) {
    res.data.forEach(cat => {
      const opt = document.createElement('option');
      opt.value = cat.id;
      opt.textContent = cat.name;
      if (selectedId && cat.id == selectedId) opt.selected = true;
      select.appendChild(opt);
    });
  }
}

function setSelectedType(type) {
  state.selectedType = type;
  document.querySelectorAll('.type-option').forEach(el => {
    el.classList.remove('active');
    if (el.dataset.type === type) el.classList.add('active');
  });
  document.getElementById('tx-type-hidden').value = type;
  loadCategoriesSelect(type);
}

function openAddModal(editData = null) {
  document.getElementById('tx-modal').classList.add('open');
  loadAddForm(editData);
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

async function handleSaveTransaction(e) {
  e.preventDefault();
  const type        = document.getElementById('tx-type-hidden').value;
  const amount      = document.getElementById('tx-amount').value;
  const description = document.getElementById('tx-desc').value.trim();
  const date        = document.getElementById('tx-date').value;
  const category_id = document.getElementById('tx-category').value;

  if (!type || !amount || !description || !date || !category_id) {
    return showToast('Lengkapi semua field!', 'error');
  }

  const image_path = document.getElementById('tx-image-path').value || null;

  const payload = { type, amount: parseFloat(amount), description, transaction_date: date, category_id: parseInt(category_id), image_path };

  let res;
  if (state.editTxId) {
    res = await api(`transactions.php?action=update&id=${state.editTxId}`, {
      method: 'PUT', body: JSON.stringify(payload)
    });
  } else {
    res = await api('transactions.php?action=create', {
      method: 'POST', body: JSON.stringify(payload)
    });
  }

  if (res.success) {
    showToast(res.message);
    closeModal('tx-modal');
    if (state.currentPage === 'transactions') loadTransactions();
    if (state.currentPage === 'dashboard') loadDashboard();
  } else {
    showToast(res.message, 'error');
  }
}

// ============================================================
// TRANSACTIONS LIST
// ============================================================
async function loadTransactions() {
  const search = document.getElementById('tx-search')?.value || '';
  const type   = document.getElementById('tx-filter-type')?.value || '';
  const month  = document.getElementById('tx-filter-month')?.value || '';
  const year   = document.getElementById('tx-filter-year')?.value || '';

  const { page, limit } = state.transactions;
  const offset = (page - 1) * limit;

  const params = new URLSearchParams({
    action: 'list', limit, offset,
    ...(search && { search }),
    ...(type   && { type }),
    ...(month  && { month }),
    ...(year   && { year }),
  });

  const res = await api(`transactions.php?${params}`);
  if (!res.success) return;

  state.transactions.data  = res.data.transactions;
  state.transactions.total = res.data.total;

  renderTransactionTable(res.data.transactions);
  renderPagination(res.data.total, page, limit);
}

function renderTransactionTable(txs) {
  const tbody = document.getElementById('tx-tbody');
  if (!txs.length) {
    tbody.innerHTML = `<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">${actionIcon('search')}</div><div class="empty-title">Tidak ada transaksi</div><div class="empty-desc">Coba ubah filter atau tambah transaksi baru</div></div></td></tr>`;
    return;
  }

  tbody.innerHTML = txs.map(tx => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px">
          <div class="tx-icon" style="width:32px;height:32px;font-size:14px;background:${tx.category_color}22;color:${tx.category_color};border-radius:8px;display:flex;align-items:center;justify-content:center">
            ${getCategoryIcon(tx.category_icon)}
          </div>
          <div>
            <div style="font-weight:500">${sanitize(tx.description)}</div>
            <div style="font-size:11px;color:var(--text-300)">${sanitize(tx.category_name)}</div>
          </div>
        </div>
      </td>
      <td><span class="badge ${tx.type}">${tx.type === 'income' ? 'Masuk' : 'Keluar'}</span></td>
      <td class="mono" style="color:${tx.type === 'income' ? 'var(--accent)' : 'var(--red)'};font-weight:600">
        ${tx.type === 'income' ? '+' : '-'}${formatRupiah(tx.amount)}
      </td>
      <td style="color:var(--text-300)">${formatDate(tx.transaction_date)}</td>
      <td>
        <div class="table-actions">
          <button class="btn btn-ghost btn-sm btn-icon" onclick="editTransaction(${tx.id})" title="Edit" aria-label="Edit transaksi">${actionIcon('edit')}</button>
          <button class="btn btn-danger btn-sm btn-icon" onclick="deleteTransaction(${tx.id})" title="Hapus" aria-label="Hapus transaksi">${actionIcon('trash')}</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderPagination(total, page, limit) {
  const totalPages = Math.ceil(total / limit);
  const start = total ? (page - 1) * limit + 1 : 0;
  const end   = Math.min(page * limit, total);

  document.getElementById('tx-pagination-info').textContent = `Menampilkan ${start}-${end} dari ${total} transaksi`;

  const btns = document.getElementById('tx-pagination-btns');
  let html = `<button class="page-btn" onclick="changePage(${page-1})" ${page===1?'disabled':''}>Prev</button>`;

  for (let i = 1; i <= totalPages; i++) {
    if (totalPages <= 7 || i === 1 || i === totalPages || Math.abs(i - page) <= 1) {
      html += `<button class="page-btn ${i===page?'active':''}" onclick="changePage(${i})">${i}</button>`;
    } else if (Math.abs(i - page) === 2) {
      html += `<button class="page-btn" disabled>...</button>`;
    }
  }

  html += `<button class="page-btn" onclick="changePage(${page+1})" ${page>=totalPages?'disabled':''}>Next</button>`;
  btns.innerHTML = html;
}

function changePage(page) {
  if (page < 1) return;
  state.transactions.page = page;
  loadTransactions();
}

async function editTransaction(id) {
  const res = await api(`transactions.php?action=get&id=${id}`);
  if (res.success) {
    openAddModal(res.data);
  } else {
    showToast(res.message, 'error');
  }
}

async function deleteTransaction(id) {
  if (!confirm('Hapus transaksi ini?')) return;
  const res = await api(`transactions.php?action=delete&id=${id}`, { method: 'DELETE' });
  if (res.success) {
    showToast(res.message);
    loadTransactions();
  } else {
    showToast(res.message, 'error');
  }
}

// ============================================================
// REPORTS
// ============================================================
async function loadReports() {
  const year = parseInt(document.getElementById('report-year')?.value) || state.currentYear;

  const [mRes, aRes] = await Promise.all([
    api(`reports.php?action=monthly&year=${year}`),
    api('reports.php?action=annual'),
  ]);

  if (mRes.success) renderMonthlyReportTable(mRes.data.monthly);
  if (aRes.success) renderAnnualReportTable(aRes.data.annual);

  // Render comparison chart
  if (mRes.success) renderComparisonChart(mRes.data.monthly);
}

function renderMonthlyReportTable(data) {
  const el = document.getElementById('monthly-report-tbody');
  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

  if (!data.length) {
    el.innerHTML = `<tr><td colspan="5" class="empty-state">Belum ada data</td></tr>`;
    return;
  }

  el.innerHTML = data.map(r => {
    const isPositive = r.balance >= 0;
    return `
      <tr>
        <td style="font-weight:500">${months[r.month-1] || r.month_name}</td>
        <td class="mono" style="color:var(--accent)">${formatRupiah(r.income)}</td>
        <td class="mono" style="color:var(--red)">${formatRupiah(r.expense)}</td>
        <td class="mono" style="color:${isPositive?'var(--accent)':'var(--red)'};font-weight:600">
          ${isPositive?'+':''}${formatRupiah(r.balance)}
        </td>
        <td style="color:var(--text-300)">${r.total_transactions} transaksi</td>
      </tr>
    `;
  }).join('');
}

function renderAnnualReportTable(data) {
  const el = document.getElementById('annual-report-tbody');
  if (!data.length) {
    el.innerHTML = `<tr><td colspan="5" class="empty-state">Belum ada data</td></tr>`;
    return;
  }

  el.innerHTML = data.map(r => {
    const isPositive = r.balance >= 0;
    return `
      <tr>
        <td style="font-weight:600">${r.year}</td>
        <td class="mono" style="color:var(--accent)">${formatRupiah(r.income)}</td>
        <td class="mono" style="color:var(--red)">${formatRupiah(r.expense)}</td>
        <td class="mono" style="color:${isPositive?'var(--accent)':'var(--red)'};font-weight:700">
          ${isPositive?'+':''}${formatRupiah(r.balance)}
        </td>
        <td style="color:var(--text-300)">${r.total_transactions} transaksi</td>
      </tr>
    `;
  }).join('');
}

function renderComparisonChart(data) {
  const ctx = document.getElementById('comparison-chart')?.getContext('2d');
  if (!ctx) return;
  if (state.charts.comparison) state.charts.comparison.destroy();

  const isDark = state.theme === 'dark';
  const gridColor = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.06)';
  const textColor = isDark ? '#8892B0' : '#4A5568';

  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  const labels  = data.map(r => months[r.month-1]);
  const income  = data.map(r => r.income);
  const expense = data.map(r => r.expense);

  state.charts.comparison = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Pemasukan', data: income,  backgroundColor: 'rgba(16,240,160,0.75)', borderRadius: 6, borderSkipped: false },
        { label: 'Pengeluaran', data: expense, backgroundColor: 'rgba(255,71,87,0.75)', borderRadius: 6, borderSkipped: false },
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: textColor, font: { family: 'DM Sans', size: 12 }, usePointStyle: true } },
        tooltip: {
          callbacks: { label: (c) => ` ${c.dataset.label}: ${formatRupiah(c.parsed.y)}` },
          backgroundColor: isDark ? '#1A2847' : '#fff',
          borderColor: isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.1)',
          borderWidth: 1,
          titleColor: isDark ? '#F0F4FF' : '#1A2847',
          bodyColor: textColor,
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: textColor } },
        y: {
          grid: { color: gridColor },
          ticks: {
            color: textColor,
            callback: (v) => 'Rp ' + (v >= 1e6 ? (v/1e6).toFixed(1)+'jt' : v >= 1e3 ? (v/1e3).toFixed(0)+'rb' : v)
          }
        }
      }
    }
  });
}

async function exportFinancialReportPdf() {
  const year = parseInt(document.getElementById('report-year')?.value) || state.currentYear;
  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    showToast('Popup diblokir. Izinkan popup untuk export PDF.', 'error');
    return;
  }

  printWindow.document.write('<!DOCTYPE html><html><head><title>Menyiapkan laporan...</title></head><body>Menyiapkan laporan...</body></html>');
  printWindow.document.close();

  const res = await api(`reports.php?action=export&year=${year}`);
  if (!res.success) {
    printWindow.close();
    showToast(res.message || 'Gagal menyiapkan laporan PDF', 'error');
    return;
  }

  const html = buildPrintableReport(res.data);
  printWindow.document.open();
  printWindow.document.write(html);
  printWindow.document.close();
  let printStarted = false;
  const openPrintDialog = () => {
    if (printStarted || printWindow.closed) return;
    printStarted = true;
    printWindow.focus();
    printWindow.print();
  };
  printWindow.onload = () => setTimeout(openPrintDialog, 250);
  setTimeout(openPrintDialog, 700);
}

function buildPrintableReport(data) {
  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const summary = data.summary || {};
  const categories = data.categories || [];
  const transactions = data.transactions || [];
  const monthly = data.monthly || [];
  const annual = data.annual || [];
  const user = data.user || {};
  const generatedAt = new Date((data.generated_at || '').replace(' ', 'T'));
  const generatedText = Number.isNaN(generatedAt.getTime())
    ? (data.generated_at || '-')
    : generatedAt.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });

  const monthlyRows = monthly.length ? monthly.map(r => `
    <tr>
      <td>${months[r.month - 1] || sanitize(r.month_name)}</td>
      <td class="num income">${formatRupiah(r.income)}</td>
      <td class="num expense">${formatRupiah(r.expense)}</td>
      <td class="num ${r.balance >= 0 ? 'income' : 'expense'}">${formatSignedRupiah(r.balance)}</td>
      <td class="num">${r.total_transactions}</td>
    </tr>
  `).join('') : '<tr><td colspan="5" class="empty-print">Belum ada data bulanan.</td></tr>';

  const categoryRows = categories.length ? categories.map(r => `
    <tr>
      <td>${r.type === 'income' ? 'Pemasukan' : 'Pengeluaran'}</td>
      <td>${sanitize(r.category_name)}</td>
      <td class="num ${r.type}">${formatRupiah(r.total)}</td>
      <td class="num">${r.total_transactions}</td>
    </tr>
  `).join('') : '<tr><td colspan="4" class="empty-print">Belum ada data kategori.</td></tr>';

  const transactionRows = transactions.length ? transactions.map(tx => `
    <tr>
      <td>${formatDate(tx.transaction_date)}</td>
      <td>${sanitize(tx.description)}</td>
      <td>${sanitize(tx.category_name)}</td>
      <td>${tx.type === 'income' ? 'Masuk' : 'Keluar'}</td>
      <td class="num ${tx.type}">${tx.type === 'income' ? '+' : '-'}${formatRupiah(tx.amount)}</td>
    </tr>
  `).join('') : '<tr><td colspan="5" class="empty-print">Belum ada transaksi pada tahun ini.</td></tr>';

  const annualRows = annual.length ? annual.map(r => `
    <tr>
      <td>${r.year}</td>
      <td class="num income">${formatRupiah(r.income)}</td>
      <td class="num expense">${formatRupiah(r.expense)}</td>
      <td class="num ${r.balance >= 0 ? 'income' : 'expense'}">${formatSignedRupiah(r.balance)}</td>
      <td class="num">${r.total_transactions}</td>
    </tr>
  `).join('') : '<tr><td colspan="5" class="empty-print">Belum ada data tahunan.</td></tr>';

  return `<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Keuangan ${data.year} - FinanceApp</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 32px; color: #111827; font-family: Arial, sans-serif; background: #fff; font-size: 12px; }
    .report { max-width: 920px; margin: 0 auto; }
    .header { display: flex; justify-content: space-between; gap: 24px; border-bottom: 3px solid #10b981; padding-bottom: 18px; margin-bottom: 22px; }
    .brand { font-size: 13px; font-weight: 700; color: #059669; text-transform: uppercase; letter-spacing: .08em; }
    h1 { margin: 8px 0 6px; font-size: 28px; line-height: 1.15; }
    .muted { color: #64748b; }
    .meta { text-align: right; line-height: 1.6; min-width: 220px; }
    .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 22px 0; }
    .card { border: 1px solid #dbe4ef; border-radius: 8px; padding: 14px; background: #f8fafc; }
    .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
    .value { font-size: 16px; font-weight: 700; }
    .income { color: #047857; }
    .expense { color: #be123c; }
    h2 { font-size: 17px; margin: 24px 0 10px; }
    table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
    th, td { padding: 9px 10px; border: 1px solid #e2e8f0; vertical-align: top; }
    th { background: #f1f5f9; color: #334155; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; }
    .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
    .empty-print { text-align: center; color: #64748b; padding: 18px; }
    .note { margin-top: 22px; padding-top: 12px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 11px; }
    @media print {
      body { padding: 16mm; }
      .report { max-width: none; }
      .card { break-inside: avoid; }
    }
  </style>
</head>
<body>
  <main class="report">
    <section class="header">
      <div>
        <div class="brand">FinanceApp</div>
        <h1>Laporan Keuangan ${data.year}</h1>
        <div class="muted">${sanitize(user.name || 'User')} ${user.email ? `&lt;${sanitize(user.email)}&gt;` : ''}</div>
      </div>
      <div class="meta">
        <div><strong>Periode:</strong> Januari - Desember ${data.year}</div>
        <div><strong>Dibuat:</strong> ${sanitize(generatedText)}</div>
      </div>
    </section>

    <section class="summary">
      <div class="card"><div class="label">Pemasukan</div><div class="value income">${formatRupiah(summary.income)}</div></div>
      <div class="card"><div class="label">Pengeluaran</div><div class="value expense">${formatRupiah(summary.expense)}</div></div>
      <div class="card"><div class="label">Saldo Bersih</div><div class="value ${summary.balance >= 0 ? 'income' : 'expense'}">${formatSignedRupiah(summary.balance)}</div></div>
      <div class="card"><div class="label">Transaksi</div><div class="value">${summary.total_transactions || 0}</div></div>
    </section>

    <h2>Laporan Bulanan</h2>
    <table>
      <thead><tr><th>Bulan</th><th>Pemasukan</th><th>Pengeluaran</th><th>Saldo</th><th>Transaksi</th></tr></thead>
      <tbody>${monthlyRows}</tbody>
    </table>

    <h2>Ringkasan Kategori</h2>
    <table>
      <thead><tr><th>Jenis</th><th>Kategori</th><th>Total</th><th>Transaksi</th></tr></thead>
      <tbody>${categoryRows}</tbody>
    </table>

    <h2>Detail Transaksi ${data.year}</h2>
    <table>
      <thead><tr><th>Tanggal</th><th>Deskripsi</th><th>Kategori</th><th>Jenis</th><th>Jumlah</th></tr></thead>
      <tbody>${transactionRows}</tbody>
    </table>

    <h2>Riwayat Tahunan</h2>
    <table>
      <thead><tr><th>Tahun</th><th>Pemasukan</th><th>Pengeluaran</th><th>Saldo</th><th>Transaksi</th></tr></thead>
      <tbody>${annualRows}</tbody>
    </table>

    <div class="note">Laporan ini dibuat otomatis dari transaksi yang tercatat di FinanceApp.</div>
  </main>
</body>
</html>`;
}

function formatSignedRupiah(n) {
  const value = Number(n || 0);
  return `${value >= 0 ? '+' : '-'}${formatRupiah(Math.abs(value))}`;
}

// ============================================================
// ICON HELPER
// ============================================================
function getCategoryIcon(icon) {
  return categorySvg(icon);
}

// ============================================================
// AMOUNT FORMATTING (auto Rupiah display)
// ============================================================
function setupAmountInput() {
  const input = document.getElementById('tx-amount');
  if (!input) return;

  input.addEventListener('input', function() {
    const raw = this.value.replace(/\D/g, '');
    const hint = document.getElementById('amount-hint');
    if (hint && raw) {
      hint.textContent = formatRupiah(raw);
    } else if (hint) {
      hint.textContent = '';
    }
  });
}

// ============================================================
// YEAR SELECTOR
// ============================================================
function buildYearOptions(selectId, currentYear) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  sel.innerHTML = '';
  for (let y = currentYear + 1; y >= currentYear - 5; y--) {
    const opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    if (y === currentYear) opt.selected = true;
    sel.appendChild(opt);
  }
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  applyTheme(state.theme);

  const cy = new Date().getFullYear();
  buildYearOptions('dash-year', cy);
  buildYearOptions('report-year', cy);

  // Build month filter
  const monthSel = document.getElementById('tx-filter-month');
  if (monthSel) {
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    months.forEach((m, i) => {
      const opt = document.createElement('option');
      opt.value = i + 1;
      opt.textContent = m;
      monthSel.appendChild(opt);
    });
  }

  const yearSel = document.getElementById('tx-filter-year');
  if (yearSel) {
    yearSel.innerHTML = '<option value="">Semua Tahun</option>';
    for (let y = cy + 1; y >= cy - 3; y--) {
      const opt = document.createElement('option');
      opt.value = y;
      opt.textContent = y;
      yearSel.appendChild(opt);
    }
  }

  setupAmountInput();
  checkAuth();
});

function uploadStrukAI() {
  const fileInput = document.getElementById('uploadStruk');

  if (!fileInput.files.length) {
    alert("Pilih gambar dulu!");
    return;
  }

  const formData = new FormData();
  formData.append('image', fileInput.files[0]);

  fetch('backend/upload_struk.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === "success") {

      const data = res.data;

      document.getElementById('tx-amount').value = data.total;

      document.getElementById('tx-image-path').value = res.image_path;

      if (data.date) {
        document.getElementById('tx-date').value = data.date;
      }

      let desc = data.items.map(i => i.name).join(', ');
      document.getElementById('tx-desc').value = desc;

      alert("Berhasil isi otomatis!");

    } else {
      alert(res.message || "AI gagal membaca");
      console.log(res.raw || res);
    }
  })
  .catch(err => {
    console.error(err);
    alert("Error koneksi");
  });
}

// ============================================================
// AI CHATBOT
// ============================================================
async function loadChatbot() {
  const box = document.getElementById('chatbot-messages');
  if (!box) return;

  const res = await api('chatbot.php?action=history');
  if (res.success) {
    renderChatbotMessages(res.data.messages || []);
  } else {
    showToast(res.message || 'Gagal memuat chat AI', 'error');
  }
}

function renderChatbotMessages(messages) {
  const box = document.getElementById('chatbot-messages');
  if (!box) return;

  if (!messages.length) {
    box.innerHTML = `
      <div class="chatbot-empty">
        <div class="chatbot-empty-icon">AI</div>
        <h3>Mulai tanya tentang uang Anda</h3>
        <p>AI akan membaca ringkasan, kategori, tren bulanan, dan transaksi terbaru dari akun ini.</p>
      </div>
    `;
    return;
  }

  box.innerHTML = messages.map(msg => `
    <div class="chatbot-message ${msg.role === 'user' ? 'user' : 'bot'}">
      <div class="chatbot-bubble">${sanitize(msg.message)}</div>
    </div>
  `).join('');
  box.scrollTop = box.scrollHeight;
}

function appendChatbotMessage(role, message) {
  const box = document.getElementById('chatbot-messages');
  if (!box) return;

  if (box.querySelector('.chatbot-empty')) box.innerHTML = '';
  const row = document.createElement('div');
  row.className = `chatbot-message ${role}`;
  row.innerHTML = `<div class="chatbot-bubble">${sanitize(message)}</div>`;
  box.appendChild(row);
  box.scrollTop = box.scrollHeight;
}

async function sendChatbotMessage(e) {
  e.preventDefault();
  const input = document.getElementById('chatbot-input');
  const btn = document.getElementById('chatbot-send');
  const message = input.value.trim();
  if (!message) return;

  input.value = '';
  appendChatbotMessage('user', message);
  appendChatbotMessage('bot loading', 'Sedang membaca data keuangan Anda...');
  btn.disabled = true;

  const res = await api('chatbot.php?action=send', {
    method: 'POST',
    body: JSON.stringify({ message }),
  });

  document.querySelector('.chatbot-message.loading')?.remove();
  btn.disabled = false;

  if (res.success) {
    appendChatbotMessage('bot', res.data.reply);
  } else {
    appendChatbotMessage('bot', res.message || 'AI belum bisa menjawab saat ini.');
    showToast(res.message || 'Chatbot error', 'error');
  }
}

async function clearChatbotHistory() {
  if (!confirm('Hapus semua riwayat chat AI?')) return;
  const res = await api('chatbot.php?action=clear', { method: 'DELETE' });
  if (res.success) {
    showToast(res.message);
    renderChatbotMessages([]);
  } else {
    showToast(res.message || 'Gagal menghapus riwayat chat', 'error');
  }
}

function insertChatPrompt(text) {
  const input = document.getElementById('chatbot-input');
  if (!input) return;
  input.value = text;
  input.focus();
}




