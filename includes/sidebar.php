<?php

require_once __DIR__ . '/auth.php';

require_login();

$current_page = basename($_SERVER['PHP_SELF']);

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
                <?= ucfirst(htmlspecialchars($_SESSION['role'])) ?>
            </span>
        </div>

    </div>


    <!-- MENU -->
    <nav class="sidebar-menu">

        <?php if ($_SESSION['role'] === 'admin'): ?>

            <div class="menu-title">
                MENU UTAMA
            </div>


            <!-- DASHBOARD -->
            <a
                href="../admin/dashboard.php"
                class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
            >
                <span class="menu-icon">▣</span>
                <span>Dashboard</span>
            </a>


            <!-- NASABAH -->
            <a
                href="../admin/nasabah/index.php"
                class="<?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], '/nasabah/') !== false ? 'active' : '' ?>"
            >
               <span class="menu-icon">♙</span>
               <span>Nasabah</span>
            </a>


            <!-- JENIS SAMPAH -->
            <a
                href="../admin/sampah/index.php"
                class="<?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], '/sampah/') !== false ? 'active' : '' ?>"
            >
                <span class="menu-icon">♻</span>
                <span>Jenis Sampah</span>
            </a>


            <!-- TRANSAKSI -->
            <a
    href="../admin/transaksi/index.php"
    class="<?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], '/transaksi/') !== false ? 'active' : '' ?>"
>
    <span class="menu-icon">⇅</span>
    <span>Transaksi Setor</span>
</a>


            <!-- PENARIKAN -->
            <a
    href="../admin/penarikan/index.php"
    class="<?= $current_page === 'index.php' && strpos($_SERVER['PHP_SELF'], '/penarikan/') !== false ? 'active' : '' ?>"
>
    <span class="menu-icon">↓</span>
    <span>Penarikan</span>
</a>


            <!-- LAPORAN -->
            <a
    href="../admin/laporan/transaksi.php"
    class="<?= $current_page === 'transaksi.php' && strpos($_SERVER['PHP_SELF'], '/laporan/') !== false ? 'active' : '' ?>"
>
    <span class="menu-icon">▤</span>
    <span>Laporan</span>
</a>

            <!-- PENGATURAN -->
            <a href="#">
                <span class="menu-icon">⚙</span>
                <span>Pengaturan</span>
            </a>


        <?php else: ?>

    <div class="menu-title">
        MENU UTAMA
    </div>

    <!-- DASHBOARD -->
    <a
        href="../nasabah/dashboard.php"
        class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>"
    >
        <span class="menu-icon">▣</span>
        <span>Dashboard</span>
    </a>

    <!-- SETOR SAMPAH -->
    <a
        href="../nasabah/setor.php"
        class="<?= $current_page === 'setor.php' ? 'active' : '' ?>"
    >
        <span class="menu-icon">♻</span>
        <span>Setor Sampah</span>
    </a>

    <!-- RIWAYAT -->
    <a
        href="../nasabah/riwayat.php"
        class="<?= $current_page === 'riwayat.php' ? 'active' : '' ?>"
    >
        <span class="menu-icon">↕</span>
        <span>Riwayat Setor</span>
    </a>

    <!-- SALDO -->
    <a
        href="../nasabah/saldo.php"
        class="<?= $current_page === 'saldo.php' ? 'active' : '' ?>"
    >
        <span class="menu-icon">💰</span>
        <span>Saldo</span>
    </a>

    <!-- PENARIKAN -->
    <a
        href="../nasabah/penarikan.php"
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
        href="../nasabah/profil.php"
        class="<?= $current_page === 'profil.php' ? 'active' : '' ?>"
    >
        <span class="menu-icon">⚙</span>
        <span>Profil</span>
    </a>

<?php endif; ?>

    </nav>


    <!-- BOTTOM -->
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


        <a
            href="../auth/logout.php"
            class="logout-link"
        >
            <span class="menu-icon">↪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>