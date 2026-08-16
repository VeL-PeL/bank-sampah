<?php

require_once __DIR__ . '/auth.php';

require_login();

$current_page = basename($_SERVER['PHP_SELF']);
$current_role = $_SESSION['role'];

?>

<aside class="sidebar">

    <!-- LOGO -->
    <div class="sidebar-logo">

        <div class="logo-icon">
            ♻
        </div>

        <div class="logo-text">
            <h2>Bank Sampah</h2>

            <span>
                <?= ucfirst(htmlspecialchars($current_role)) ?>
            </span>
        </div>

    </div>


    <!-- MENU -->
    <nav class="sidebar-menu">

        <?php if ($current_role === 'admin'): ?>

            <!-- ========================= -->
            <!-- MENU ADMIN -->
            <!-- ========================= -->

            <div class="menu-title">
                MENU UTAMA
            </div>


            <!-- DASHBOARD -->
            <a
                href="/bank-sampah/admin/dashboard.php"
                class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">▣</span>
                <span>Dashboard</span>
            </a>


            <!-- NASABAH -->
            <a
                href="/bank-sampah/admin/nasabah/index.php"
            >
                <span class="menu-icon">♙</span>
                <span>Nasabah</span>
            </a>


            <!-- JENIS SAMPAH -->
            <a
                href="/bank-sampah/admin/jenis_sampah/index.php"
            >
                <span class="menu-icon">♻</span>
                <span>Jenis Sampah</span>
            </a>


            <!-- TRANSAKSI SETOR -->
            <a
                href="/bank-sampah/admin/transaksi-setor/index.php"
            >
                <span class="menu-icon">⇅</span>
                <span>Transaksi Setor</span>
            </a>


            <!-- PENARIKAN -->
            <a
                href="/bank-sampah/admin/penarikan/index.php"
            >
                <span class="menu-icon">↓</span>
                <span>Penarikan</span>
            </a>


            <!-- LAPORAN -->
            <a
                href="/bank-sampah/admin/laporan/index.php"
            >
                <span class="menu-icon">▤</span>
                <span>Laporan</span>
            </a>


            <div class="menu-title">
                MANAJEMEN
            </div>


            <!-- PENGATURAN -->
            <a
                href="/bank-sampah/admin/pengaturan/index.php"
            >
                <span class="menu-icon">⚙</span>
                <span>Pengaturan</span>
            </a>


        <?php else: ?>

            <!-- ========================= -->
            <!-- MENU NASABAH -->
            <!-- ========================= -->

            <div class="menu-title">
                MENU UTAMA
            </div>


            <!-- DASHBOARD -->
            <a
                href="/bank-sampah/nasabah/dashboard.php"
                class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">▣</span>
                <span>Dashboard</span>
            </a>


            <!-- SETOR SAMPAH -->
            <a
                href="/bank-sampah/nasabah/setor.php"
                class="<?= $current_page === 'setor.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">♻</span>
                <span>Setor Sampah</span>
            </a>


            <!-- RIWAYAT -->
            <a
                href="/bank-sampah/nasabah/riwayat.php"
                class="<?= $current_page === 'riwayat.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">↕</span>
                <span>Riwayat Setor</span>
            </a>


            <!-- SALDO -->
            <a
                href="/bank-sampah/nasabah/saldo.php"
                class="<?= $current_page === 'saldo.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">💰</span>
                <span>Saldo</span>
            </a>


            <!-- PENARIKAN -->
            <a
                href="/bank-sampah/nasabah/penarikan.php"
                class="<?= $current_page === 'penarikan.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">↓</span>
                <span>Penarikan</span>
            </a>


            <div class="menu-title">
                AKUN
            </div>


            <!-- PROFIL -->
            <a
                href="/bank-sampah/nasabah/profil.php"
                class="<?= $current_page === 'profil.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">⚙</span>
                <span>Profil</span>
            </a>

        <?php endif; ?>

    </nav>


    <!-- ========================= -->
    <!-- USER BOTTOM -->
    <!-- ========================= -->

    <div class="sidebar-bottom">

        <div class="sidebar-user">

            <div class="sidebar-user-avatar">

                <?= strtoupper(
                    substr(
                        $_SESSION['nama'],
                        0,
                        1
                    )
                ) ?>

            </div>


            <div class="sidebar-user-info">

                <strong>
                    <?= htmlspecialchars($_SESSION['nama']) ?>
                </strong>

                <span>
                    <?= ucfirst(
                        htmlspecialchars($_SESSION['role'])
                    ) ?>
                </span>

            </div>

        </div>


        <!-- LOGOUT -->
        <a
            href="/bank-sampah/auth/logout.php"
            class="logout-link"
        >
            <span class="menu-icon">↪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>