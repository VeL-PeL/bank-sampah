<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

$page_title = 'Profil';

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';


// ========================================
// DATA USER
// ========================================

$nama = $_SESSION['nama'] ?? 'Nasabah';
$email = $_SESSION['email'] ?? '-';

$nama = trim($nama);

$initial = strtoupper(
    mb_substr($nama, 0, 1)
);

?>

<main class="main-content">

    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Profil
                </h1>

                <p>
                    Kelola dan lihat informasi akun kamu.
                </p>

            </div>

        </div>


        <div class="topbar-right">

            <button
                type="button"
                class="notification-btn"
                title="Notifikasi"
            >
                🔔

                <span class="notification-badge">
                    0
                </span>

            </button>


            <div class="user-info">

                <div class="user-avatar">
                    <?= htmlspecialchars($initial) ?>
                </div>

                <div class="user-details">

                    <div class="user-name">
                        <?= htmlspecialchars($nama) ?>
                    </div>

                    <div class="user-role">
                        Nasabah
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- ==================================================
         CONTENT
    ================================================== -->

    <div class="profile-container">


        <!-- ==================================================
             PROFILE CARD
        ================================================== -->

        <div class="profile-card">


            <!-- COVER -->

            <div class="profile-cover">

                <div class="cover-icon">
                    ♻
                </div>

            </div>


            <!-- PROFILE NAME -->

            <div class="profile-header">

                <div class="profile-name">

                    <h2>
                        <?= htmlspecialchars($nama) ?>
                    </h2>

                    <span>
                        Nasabah Bank Sampah
                    </span>

                </div>

            </div>


            <!-- ==================================================
                 PROFILE INFORMATION
            ================================================== -->

            <div class="profile-info">


                <!-- NAMA -->

                <div class="info-item">

                    <div class="info-icon">
                        👤
                    </div>

                    <div class="info-content">

                        <span>
                            Nama Lengkap
                        </span>

                        <strong>
                            <?= htmlspecialchars($nama) ?>
                        </strong>

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="info-item">

                    <div class="info-icon">
                        ✉
                    </div>

                    <div class="info-content">

                        <span>
                            Email
                        </span>

                        <strong>
                            <?= htmlspecialchars($email) ?>
                        </strong>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="info-item">

                    <div class="info-icon">
                        ♻
                    </div>

                    <div class="info-content">

                        <span>
                            Status Akun
                        </span>

                        <strong class="status-active">
                            Aktif
                        </strong>

                    </div>

                </div>


                <!-- TIPE AKUN -->

                <div class="info-item">

                    <div class="info-icon">
                        ♡
                    </div>

                    <div class="info-content">

                        <span>
                            Tipe Akun
                        </span>

                        <strong>
                            Nasabah
                        </strong>

                    </div>

                </div>


            </div>

        </div>


        <!-- ==================================================
             INFORMASI AKUN
        ================================================== -->

        <div class="profile-info-card">

            <div class="info-card-header">

                <div class="info-card-icon">
                    💡
                </div>

                <div>

                    <h3>
                        Informasi Akun
                    </h3>

                    <p>
                        Data akun yang sedang digunakan.
                    </p>

                </div>

            </div>


            <div class="account-note">

                <div class="account-note-icon">
                    ✓
                </div>

                <p>
                    Pastikan informasi akun kamu tetap benar
                    dan dapat digunakan untuk keperluan
                    transaksi Bank Sampah.
                </p>

            </div>

        </div>


        <!-- ==================================================
             QUICK MENU
        ================================================== -->

        <div class="quick-menu">


            <!-- SALDO -->

            <a
                href="saldo.php"
                class="quick-card"
            >

                <div class="quick-icon">
                    💰
                </div>

                <div class="quick-content">

                    <strong>
                        Saldo Saya
                    </strong>

                    <span>
                        Lihat saldo tabungan
                    </span>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>


            <!-- RIWAYAT -->

            <a
                href="riwayat.php"
                class="quick-card"
            >

                <div class="quick-icon">
                    ↕
                </div>

                <div class="quick-content">

                    <strong>
                        Riwayat Setor
                    </strong>

                    <span>
                        Lihat riwayat setoran
                    </span>

                </div>

                <div class="quick-arrow">
                    →
                </div>

            </a>


        </div>


    </div>

</main>


<style>

/* ======================================================
   PROFILE CONTAINER
====================================================== */

.profile-container {

    width: 100%;
    max-width: 900px;

    margin-top: 25px;

}


/* ======================================================
   PROFILE CARD
====================================================== */

.profile-card {

    width: 100%;

    overflow: hidden;

    background: #ffffff;

    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(0, 0, 0, 0.05);

}


/* ======================================================
   COVER
====================================================== */

.profile-cover {

    position: relative;

    height: 110px;

    background:
        linear-gradient(
            135deg,
            #166534 0%,
            #16a34a 100%
        );

    overflow: hidden;

}


/* Dekorasi cover */

.profile-cover::before {

    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    right: -50px;
    top: -80px;

    border-radius: 50%;

    background:
        rgba(255, 255, 255, 0.06);

}


.profile-cover::after {

    content: "";

    position: absolute;

    width: 110px;
    height: 110px;

    left: -40px;
    bottom: -65px;

    border-radius: 50%;

    background:
        rgba(255, 255, 255, 0.05);

}


/* Icon recycle */

.cover-icon {

    position: absolute;

    right: 35px;
    top: 17px;

    color:
        rgba(255, 255, 255, 0.10);

    font-size: 65px;

    line-height: 1;

}


/* ======================================================
   PROFILE HEADER
====================================================== */

.profile-header {

    display: flex;

    align-items: center;

    min-height: 105px;

    padding: 25px 30px;

    border-bottom:
        1px solid #f1f5f9;

}


/* ======================================================
   PROFILE NAME
====================================================== */

.profile-name {

    padding: 0;

}


.profile-name h2 {

    margin: 0 0 6px;

    color:
        #1f2937;

    font-size: 23px;

    font-weight: 700;

    line-height: 1.2;

}


.profile-name span {

    color:
        #64748b;

    font-size: 13px;

}


/* ======================================================
   PROFILE INFORMATION
====================================================== */

.profile-info {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 1px;

    background:
        #f1f5f9;

}


.info-item {

    display: flex;

    align-items: center;

    gap: 13px;

    min-width: 0;

    padding: 20px;

    background:
        #ffffff;

}


.info-icon {

    width: 44px;
    height: 44px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background:
        #f0fdf4;

    font-size: 18px;

}


.info-content {

    min-width: 0;

}


.info-content span {

    display: block;

    margin-bottom: 5px;

    color:
        #94a3b8;

    font-size: 11px;

}


.info-content strong {

    display: block;

    overflow-wrap: anywhere;

    color:
        #374151;

    font-size: 13px;

    font-weight: 700;

}


.status-active {

    color:
        #16a34a !important;

}


/* ======================================================
   INFORMASI AKUN
====================================================== */

.profile-info-card {

    margin-top: 20px;

    padding: 22px;

    background:
        #ffffff;

    border-radius: 15px;

    box-shadow:
        0 7px 22px rgba(0, 0, 0, 0.05);

}


.info-card-header {

    display: flex;

    align-items: center;

    gap: 12px;

}


.info-card-icon {

    width: 42px;
    height: 42px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background:
        #f0fdf4;

    font-size: 18px;

}


.info-card-header h3 {

    margin: 0 0 4px;

    color:
        #166534;

    font-size: 17px;

}


.info-card-header p {

    margin: 0;

    color:
        #94a3b8;

    font-size: 12px;

}


.account-note {

    display: flex;

    align-items: center;

    gap: 10px;

    margin-top: 18px;

    padding: 13px;

    border-radius: 9px;

    background:
        #f0fdf4;

}


.account-note-icon {

    width: 30px;
    height: 30px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 7px;

    background:
        #dcfce7;

    color:
        #166534;

    font-size: 14px;

    font-weight: 800;

}


.account-note p {

    margin: 0;

    color:
        #64748b;

    font-size: 12px;

    line-height: 1.5;

}


/* ======================================================
   QUICK MENU
====================================================== */

.quick-menu {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

    margin-top: 20px;

}


.quick-card {

    display: flex;

    align-items: center;

    gap: 12px;

    min-width: 0;

    padding: 17px;

    background:
        #ffffff;

    border-radius: 13px;

    color:
        #1f2937;

    text-decoration: none;

    box-shadow:
        0 7px 22px rgba(0, 0, 0, 0.05);

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;

}


.quick-card:hover {

    transform:
        translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0, 0, 0, 0.08);

}


.quick-icon {

    width: 44px;
    height: 44px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background:
        #dcfce7;

    font-size: 18px;

}


.quick-content {

    min-width: 0;

}


.quick-content strong {

    display: block;

    margin-bottom: 4px;

    color:
        #1f2937;

    font-size: 13px;

}


.quick-content span {

    display: block;

    color:
        #94a3b8;

    font-size: 11px;

}


.quick-arrow {

    margin-left: auto;

    color:
        #166534;

    font-size: 19px;

}


/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 700px) {

    .profile-container {

        margin-top: 20px;

    }


    .profile-header {

        padding-left: 20px;
        padding-right: 20px;

    }


    .profile-info {

        grid-template-columns: 1fr;

    }


    .quick-menu {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 450px) {

    .profile-cover {

        height: 95px;

    }


    .profile-header {

        min-height: auto;

        padding-top: 22px;
        padding-bottom: 22px;

    }


    .profile-name h2 {

        font-size: 19px;

    }


    .info-item {

        padding: 16px;

    }


    .profile-info-card {

        padding: 18px;

    }


    .quick-card {

        padding: 15px;

    }

}

</style>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>