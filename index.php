<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FinanceApp - Manajemen Keuangan Pribadi</title>
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%2310F0A0'/%3E%3Cpath d='M22 32h56v36H22zM22 42h56M34 58h18' fill='none' stroke='%230A0F1E' stroke-width='7' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="frontend/css/style.css?v=fix6">
</head>
<body>

<!-- ============================================================
     LOADING OVERLAY
     ============================================================ -->
<div class="loading-overlay" id="loading-overlay">
  <div class="spinner"></div>
</div>

<!-- ============================================================
     TOAST CONTAINER
     ============================================================ -->
<div class="toast-container" id="toast-container"></div>

<!-- ============================================================
     AUTH SECTION
     ============================================================ -->
<section id="auth-section" style="display:none">
  <div class="auth-wrapper">
    <div class="auth-bg-orb orb-1"></div>
    <div class="auth-bg-orb orb-2"></div>

    <!-- LOGIN FORM -->
    <div id="login-form-wrap">
      <div class="auth-card">
        <div class="auth-logo">
          <div class="auth-logo-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/><path d="M7 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
          <div class="auth-logo-text">FinanceApp</div>
        </div>
        <h1 class="auth-title">Selamat Datang</h1>
        <p class="auth-subtitle">Masuk untuk mengelola keuangan Anda</p>

        <form id="login-form" onsubmit="handleLogin(event)">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" id="login-email" class="form-control" placeholder="nama@email.com" autocomplete="email" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" id="login-password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
              <span class="input-group-append" onclick="togglePassword('login-password',this)" data-visible="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></svg></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px">Masuk</button>
        </form>

        <div class="auth-divider"><span>atau</span></div>
        <button type="button" class="btn btn-google" onclick="handleGoogleLogin()">
          <span class="google-mark" aria-hidden="true">G</span>
          Masuk dengan Google
        </button>

        <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-300)">
          Belum punya akun?
          <a onclick="showAuth('register')" style="color:var(--accent);cursor:pointer;font-weight:500">Daftar sekarang</a>
        </p>
      </div>
    </div>

    <!-- REGISTER FORM -->
    <div id="register-form-wrap" style="display:none">
      <div class="auth-card">
        <div class="auth-logo">
          <div class="auth-logo-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/><path d="M7 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
          <div class="auth-logo-text">FinanceApp</div>
        </div>
        <h1 class="auth-title">Buat Akun Baru</h1>
        <p class="auth-subtitle">Mulai perjalanan keuangan Anda hari ini</p>

        <form id="register-form" onsubmit="handleRegister(event)">
          <div class="form-group">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" id="reg-name" class="form-control" placeholder="John Doe" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" id="reg-email" class="form-control" placeholder="nama@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-group">
              <input type="password" id="reg-password" class="form-control" placeholder="Min. 6 karakter" required>
              <span class="input-group-append" onclick="togglePassword('reg-password',this)" data-visible="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></svg></span>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" id="reg-confirm" class="form-control" placeholder="Ulangi password" required>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:8px">Daftar Sekarang</button>
        </form>

        <div class="auth-divider"><span>atau</span></div>
        <button type="button" class="btn btn-google" onclick="handleGoogleLogin()">
          <span class="google-mark" aria-hidden="true">G</span>
          Daftar dengan Google
        </button>

        <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--text-300)">
          Sudah punya akun?
          <a onclick="showAuth('login')" style="color:var(--accent);cursor:pointer;font-weight:500">Masuk</a>
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     APP SECTION
     ============================================================ -->
<section id="app-section" style="display:none" class="app-layout">

  <!-- Sidebar Overlay (mobile) -->
  <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileSidebar()"></div>

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 10h18" stroke="currentColor" stroke-width="2"/><path d="M7 15h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
      <div class="sidebar-logo-name">FinanceApp</div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Menu Utama</div>

      <div class="nav-item active" data-page="dashboard" onclick="navigateTo('dashboard')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </div>

      <div class="nav-item" data-page="transactions" onclick="navigateTo('transactions')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Daftar Transaksi
      </div>

      <div class="nav-item" data-page="reports" onclick="navigateTo('reports')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Laporan
      </div>

      <div class="nav-item" data-page="chatbot" onclick="navigateTo('chatbot')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
        AI Keuangan
      </div>
      <div class="nav-section-label" style="margin-top:8px">Aksi Cepat</div>

      <div class="nav-item" onclick="openAddModal()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Tambah Transaksi
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="user-card" onclick="confirmLogout()">
        <div class="user-avatar">U</div>
        <div class="user-info">
          <div class="user-name">User</div>
          <div class="user-email">user@email.com</div>
        </div>
        <span style="color:var(--text-500);font-size:13px">Logout</span>
      </div>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button class="hamburger" id="hamburger" onclick="toggleMobileSidebar()" aria-label="Buka menu"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        <h1 class="topbar-title" id="topbar-page">Dashboard</h1>
      </div>
      <div class="topbar-actions">
        <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle tema" aria-label="Toggle tema"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
        <button class="btn btn-primary btn-sm" onclick="openAddModal()">+ Tambah</button>
        <span style="font-size:13px;color:var(--text-300)" id="topbar-user"></span>
      </div>
    </header>

    <!-- PAGE CONTENT -->
    <div class="page-content">

      <!-- ================================================
           PAGE: DASHBOARD
           ================================================ -->
      <div class="page active" id="page-dashboard">

        <!-- Summary Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
          <div>
            <h2 style="font-size:22px;font-weight:700">Ringkasan Keuangan</h2>
            <p style="font-size:13px;color:var(--text-300)">Overview keuangan Anda secara real-time</p>
          </div>
          <div style="display:flex;align-items:center;gap:10px">
            <select class="year-select" id="dash-year" onchange="state.currentYear=parseInt(this.value);loadDashboard()"></select>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card income">
            <div class="stat-header">
              <span class="stat-label">Total Pemasukan</span>
              <div class="stat-icon income-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 17l6-6 4 4 6-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 7h6v6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            </div>
            <div class="stat-value mono" id="stat-income">Rp 0</div>
            <div class="stat-sub">Tahun ini</div>
          </div>
          <div class="stat-card expense">
            <div class="stat-header">
              <span class="stat-label">Total Pengeluaran</span>
              <div class="stat-icon expense-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7l6 6 4-4 6 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 17h6v-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            </div>
            <div class="stat-value mono" id="stat-expense">Rp 0</div>
            <div class="stat-sub">Tahun ini</div>
          </div>
          <div class="stat-card balance">
            <div class="stat-header">
              <span class="stat-label">Saldo Bersih</span>
              <div class="stat-icon balance-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M16 13h5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div>
            </div>
            <div class="stat-value mono" id="stat-balance">Rp 0</div>
            <div class="stat-sub" id="balance-sub">Pemasukan - Pengeluaran</div>
          </div>
          <div class="stat-card count">
            <div class="stat-header">
              <span class="stat-label">Total Transaksi</span>
              <div class="stat-icon count-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 6h.01M3 12h.01M3 18h.01" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg></div>
            </div>
            <div class="stat-value" id="stat-count" style="font-size:22px">0</div>
            <div class="stat-sub">Tahun ini</div>
          </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
          <div class="chart-card">
            <div class="chart-header">
              <div>
                <div class="chart-title">Tren Keuangan Bulanan</div>
                <div class="chart-subtitle">Pemasukan vs Pengeluaran</div>
              </div>
            </div>
            <div style="height:260px;position:relative">
              <canvas id="monthly-chart"></canvas>
            </div>
          </div>

          <div class="chart-card">
            <div class="chart-header">
              <div class="chart-title">Distribusi Kategori</div>
              <div class="donut-tabs">
                <button class="donut-tab active" onclick="switchDonut('expense',this)">Pengeluaran</button>
                <button class="donut-tab" onclick="switchDonut('income',this)">Pemasukan</button>
              </div>
            </div>
            <div style="height:200px;position:relative">
              <canvas id="donut-chart"></canvas>
            </div>
            <div class="chart-legend" id="donut-legend"></div>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="chart-card">
          <div class="section-header">
            <div class="section-title">Transaksi Terbaru</div>
            <button class="btn btn-ghost btn-sm" onclick="navigateTo('transactions')">Lihat Semua</button>
          </div>
          <div class="transactions-list" id="recent-list">
            <div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M21 12a9 9 0 0 0-9-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div><div class="empty-title">Memuat...</div></div>
          </div>
        </div>
      </div>

      <!-- ================================================
           PAGE: TRANSACTIONS
           ================================================ -->
      <div class="page" id="page-transactions">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
          <div>
            <h2 style="font-size:22px;font-weight:700">Daftar Transaksi</h2>
            <p style="font-size:13px;color:var(--text-300)">Kelola semua transaksi Anda</p>
          </div>
          <button class="btn btn-primary btn-sm" onclick="openAddModal()">+ Tambah Transaksi</button>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
          <div class="search-box">
            <span class="search-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-4-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
            <input type="text" id="tx-search" class="form-control" placeholder="Cari transaksi..." oninput="debounceSearch()">
          </div>
          <select id="tx-filter-type" class="form-control" style="width:150px" onchange="resetAndLoad()">
            <option value="">Semua Jenis</option>
            <option value="income">Pemasukan</option>
            <option value="expense">Pengeluaran</option>
          </select>
          <select id="tx-filter-month" class="form-control" style="width:150px" onchange="resetAndLoad()">
            <option value="">Semua Bulan</option>
          </select>
          <select id="tx-filter-year" class="form-control" style="width:130px" onchange="resetAndLoad()"></select>
          <button class="btn btn-secondary btn-sm" onclick="clearFilters()">Reset</button>
        </div>

        <!-- Table -->
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Transaksi</th>
                <th>Jenis</th>
                <th>Jumlah</th>
                <th>Tanggal</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tx-tbody">
              <tr><td colspan="5"><div class="empty-state"><div class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2" opacity="0.35"/><path d="M21 12a9 9 0 0 0-9-9" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div><div class="empty-title">Memuat...</div></div></td></tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="pagination">
            <span id="tx-pagination-info">-</span>
            <div class="pagination-btns" id="tx-pagination-btns"></div>
          </div>
        </div>
      </div>

      <!-- ================================================
           PAGE: REPORTS
           ================================================ -->
      <div class="page" id="page-reports">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
          <div>
            <h2 style="font-size:22px;font-weight:700">Laporan Keuangan</h2>
            <p style="font-size:13px;color:var(--text-300)">Analisis mendalam keuangan Anda</p>
          </div>
          <div class="report-actions">
            <select class="year-select" id="report-year" onchange="loadReports()"></select>
            <button class="btn btn-secondary btn-sm report-export-btn" onclick="exportFinancialReportPdf()" title="Export PDF" aria-label="Export laporan ke PDF">
              <span class="btn-icon-inline" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3v11" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M7 10l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span>
              Export PDF
            </button>
          </div>
        </div>

        <!-- Comparison Chart -->
        <div class="chart-card" style="margin-bottom:20px">
          <div class="chart-header">
            <div>
              <div class="chart-title">Perbandingan Pemasukan vs Pengeluaran</div>
              <div class="chart-subtitle">Per bulan dalam tahun yang dipilih</div>
            </div>
          </div>
          <div style="height:280px;position:relative">
            <canvas id="comparison-chart"></canvas>
          </div>
        </div>

        <div class="reports-grid">
          <!-- Monthly Report Table -->
          <div class="report-table-card">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border)">
              <div class="chart-title">Laporan Bulanan</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Bulan</th>
                  <th>Pemasukan</th>
                  <th>Pengeluaran</th>
                  <th>Saldo</th>
                  <th>Transaksi</th>
                </tr>
              </thead>
              <tbody id="monthly-report-tbody">
                <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-300)">Memuat...</td></tr>
              </tbody>
            </table>
          </div>

          <!-- Annual Report Table -->
          <div class="report-table-card">
            <div style="padding:18px 20px;border-bottom:1px solid var(--border)">
              <div class="chart-title">Laporan Tahunan</div>
            </div>
            <table>
              <thead>
                <tr>
                  <th>Tahun</th>
                  <th>Pemasukan</th>
                  <th>Pengeluaran</th>
                  <th>Saldo</th>
                  <th>Transaksi</th>
                </tr>
              </thead>
              <tbody id="annual-report-tbody">
                <tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-300)">Memuat...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ================================================
           PAGE: AI KEUANGAN
           ================================================ -->
      <div class="page" id="page-chatbot">
        <div class="chatbot-shell">
          <section class="chatbot-main">
            <div class="chatbot-header">
              <div>
                <h2>AI Keuangan Pribadi</h2>
                <p>Tanyakan transaksi yang sudah terjadi atau rencana keuangan berikutnya.</p>
              </div>
              <button class="btn btn-secondary btn-sm" onclick="clearChatbotHistory()">Hapus Riwayat</button>
            </div>

            <div class="chatbot-messages" id="chatbot-messages">
              <div class="chatbot-empty">
                <div class="chatbot-empty-icon">AI</div>
                <h3>Mulai tanya tentang uang Anda</h3>
                <p>AI akan membaca ringkasan, kategori, tren bulanan, dan transaksi terbaru dari akun ini.</p>
              </div>
            </div>

            <form class="chatbot-form" action="javascript:void(0)" onsubmit="return false;">
              <textarea id="chatbot-input" class="form-control" rows="2" maxlength="1200" placeholder="Contoh: bulan ini pengeluaran terbesar saya apa? Kalau saya beli laptop 4 juta, saldo masih aman?" required></textarea>
              <button id="chatbot-send" class="btn btn-primary chatbot-send" type="button" onclick="sendChatbotMessage(event)">Kirim</button>
            </form>
          </section>

          <aside class="chatbot-side">
            <div class="chatbot-side-title">Contoh Pertanyaan</div>
            <button type="button" onclick="insertChatPrompt('Bulan ini pengeluaran terbesar saya di kategori apa?')">Pengeluaran terbesar bulan ini</button>
            <button type="button" onclick="insertChatPrompt('Analisis kondisi keuangan saya dari transaksi yang sudah tercatat.')">Analisis kondisi keuangan</button>
            <button type="button" onclick="insertChatPrompt('Kalau saya ingin menabung 2 juta bulan depan, bagian mana yang perlu dikurangi?')">Rencana menabung</button>
            <button type="button" onclick="insertChatPrompt('Buatkan saran budget bulan depan berdasarkan kebiasaan transaksi saya.')">Budget bulan depan</button>
          </aside>
        </div>
      </div>

    </div><!-- /page-content -->
  </main>
</section>

<!-- ============================================================
     MODAL: TAMBAH / EDIT TRANSAKSI
     ============================================================ -->
<div class="modal-overlay" id="tx-modal" onclick="handleModalOutsideClick(event,'tx-modal')">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-tx-title">Tambah Transaksi</h3>
      <button class="modal-close" onclick="closeModal('tx-modal')" aria-label="Tutup"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
    </div>

    <form id="tx-form" onsubmit="handleSaveTransaction(event)">
      <!-- Type Selector -->
      <div class="form-group">
        <label class="form-label">Jenis Transaksi</label>
        <div class="type-selector">
          <div class="type-option active expense" data-type="expense" onclick="setSelectedType('expense')"><span class="type-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7l6 6 4-4 6 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Pengeluaran</div>
          <div class="type-option income" data-type="income" onclick="setSelectedType('income')"><span class="type-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 17l6-6 4 4 6-8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>Pemasukan</div>
        </div>
        <input type="hidden" id="tx-type-hidden" value="expense">
        <input type="hidden" id="tx-image-path">
      </div>

      <div class="form-group">
        <label class="form-label">Scan Struk (AI)</label>
        <input type="file" id="uploadStruk">
        <button type="button" class="btn btn-secondary btn-sm" onclick="uploadStrukAI()">Scan Otomatis</button>
      </div>

      <div class="form-group">
        <label class="form-label">Jumlah (Rp)</label>
        <input type="number" id="tx-amount" class="form-control" placeholder="Contoh: 50000" min="1" step="any" required>
        <div class="rupiah-hint" id="amount-hint"></div>
      </div>

      <div class="form-group">
        <label class="form-label">Deskripsi</label>
        <input type="text" id="tx-desc" class="form-control" placeholder="Contoh: Makan siang di kantin" maxlength="255" required>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div class="form-group">
          <label class="form-label">Tanggal</label>
          <input type="date" id="tx-date" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Kategori</label>
          <select id="tx-category" class="form-control" required>
            <option value="">Pilih kategori...</option>
          </select>
        </div>
      </div>

      <div style="display:flex;gap:10px;margin-top:8px">
        <button type="button" class="btn btn-secondary" style="flex:1" onclick="closeModal('tx-modal')">Batal</button>
        <button type="submit" class="btn btn-primary" style="flex:2">Simpan Transaksi</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->
<script src="vendor/chartjs/chart.min.js"></script>
<script type="module" src="frontend/js/firebase-auth.js?v=fix6"></script>
<script src="frontend/js/app.js?v=fix6"></script>
<script>
// ---- Extra UI helpers ----

function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  const visible = input.type === 'password';
  input.type = visible ? 'text' : 'password';
  btn.dataset.visible = visible ? 'true' : 'false';
}

function switchDonut(type, btn) {
  state.donutType = type;
  document.querySelectorAll('.donut-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  const year = state.currentYear;
  api(`dashboard.php?action=category_chart&year=${year}&type=${type}`)
    .then(res => { if (res.success) renderDonutChart(res.data); });
}

function handleModalOutsideClick(e, modalId) {
  if (e.target.id === modalId) closeModal(modalId);
}

function confirmLogout() {
  if (confirm('Yakin ingin logout?')) handleLogout();
}

function toggleMobileSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebar-overlay').classList.toggle('show');
}

function closeMobileSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebar-overlay').classList.remove('show');
}

// Search debounce
let searchTimer;
function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    state.transactions.page = 1;
    loadTransactions();
  }, 400);
}

function resetAndLoad() {
  state.transactions.page = 1;
  loadTransactions();
}

function clearFilters() {
  document.getElementById('tx-search').value = '';
  document.getElementById('tx-filter-type').value = '';
  document.getElementById('tx-filter-month').value = '';
  document.getElementById('tx-filter-year').value = '';
  resetAndLoad();
}

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
  if (e.altKey && e.key === 'n') { e.preventDefault(); openAddModal(); }
  if (e.altKey && e.key === 'd') { e.preventDefault(); navigateTo('dashboard'); }
  if (e.altKey && e.key === 't') { e.preventDefault(); navigateTo('transactions'); }
  if (e.altKey && e.key === 'r') { e.preventDefault(); navigateTo('reports'); }
});
</script>
</body>
</html>






