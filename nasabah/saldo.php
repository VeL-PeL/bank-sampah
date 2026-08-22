<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

require_once __DIR__ . '/../config/database.php';

$page_title = 'Saldo Saya';


// ========================================
// AMBIL DATA SALDO NASABAH
// ========================================

$saldo = 0;

try {

    /*
     * Session user yang sedang login
     */
    $user_id = $_SESSION['user_id'] ?? null;


    if ($user_id) {

        /*
         * Cari saldo berdasarkan user yang login.
         *
         * users
         *   ↓
         * nasabah
         *   ↓
         * saldo
         */

        $stmt = $pdo->prepare("
            SELECT s.saldo
            FROM saldo s
            INNER JOIN nasabah n
                ON n.id = s.nasabah_id
            WHERE n.user_id = ?
            LIMIT 1
        ");

        $stmt->execute([$user_id]);

        $data_saldo = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($data_saldo) {

            $saldo = (float) $data_saldo['saldo'];

        }

    }

} catch (PDOException $e) {

    $saldo = 0;

}


// ========================================
// HEADER & SIDEBAR
// ========================================

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../includes/sidebar.php';

?>


<main class="main-content">


    <!-- ==================================================
         TOPBAR
    ================================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Saldo Saya
                </h1>

                <p>
                    Lihat dan kelola saldo tabungan Bank Sampah kamu.
                </p>

            </div>

        </div>


        <div class="topbar-right">

            <!-- NOTIFICATION -->

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


            <!-- USER -->

            <div class="user-info">

                <div class="user-avatar">

                    <?= strtoupper(
                        substr(
                            $_SESSION['nama'],
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama']
                        ) ?>

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

    <div class="saldo-container">


        <!-- ==================================================
             SALDO CARD
        ================================================== -->

        <div class="saldo-card">

            <div class="saldo-card-top">

                <div>

                    <span class="saldo-label">
                        SALDO SAYA
                    </span>

                    <p class="saldo-title">
                        Saldo Tabungan
                    </p>

                </div>


                <div class="saldo-icon">
                    💰
                </div>

            </div>


            <div class="saldo-value">

                Rp <?= number_format(
                    $saldo,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>


            <div class="saldo-footer">

                <span>
                    ♻ Bank Sampah
                </span>

                <span>
                    Saldo aktif
                </span>

            </div>

        </div>



        <!-- ==================================================
             QUICK ACTION
        ================================================== -->

        <div class="saldo-actions">


            <a
                href="penarikan.php"
                class="saldo-action"
            >

                <div class="action-icon">
                    ↓
                </div>

                <div>

                    <strong>
                        Tarik Saldo
                    </strong>

                    <span>
                        Ajukan penarikan saldo
                    </span>

                </div>

                <div class="action-arrow">
                    →
                </div>

            </a>


            <a
                href="riwayat.php"
                class="saldo-action"
            >

                <div class="action-icon">
                    ↕
                </div>

                <div>

                    <strong>
                        Riwayat Setor
                    </strong>

                    <span>
                        Lihat transaksi setoran
                    </span>

                </div>

                <div class="action-arrow">
                    →
                </div>

            </a>


        </div>



        <!-- ==================================================
             INFORMASI
        ================================================== -->

        <div class="info-card">


            <div class="info-header">

                <div class="info-icon">
                    💡
                </div>

                <div>

                    <h2>
                        Informasi Saldo
                    </h2>

                    <p>
                        Ketahui bagaimana saldo kamu bertambah.
                    </p>

                </div>

            </div>


            <div class="info-list">


                <div class="info-item">

                    <div class="info-number">
                        1
                    </div>

                    <div>

                        <strong>
                            Setor Sampah
                        </strong>

                        <p>
                            Ajukan setoran sampah melalui menu
                            Setor Sampah.
                        </p>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-number">
                        2
                    </div>

                    <div>

                        <strong>
                            Diproses Admin
                        </strong>

                        <p>
                            Admin akan memeriksa dan memproses
                            setoran kamu.
                        </p>

                    </div>

                </div>


                <div class="info-item">

                    <div class="info-number">
                        3
                    </div>

                    <div>

                        <strong>
                            Saldo Bertambah
                        </strong>

                        <p>
                            Setelah setoran diterima, saldo kamu
                            akan bertambah sesuai nilai setoran.
                        </p>

                    </div>

                </div>


            </div>

        </div>



        <!-- ==================================================
             CATATAN
        ================================================== -->

        <div class="saldo-note">

            <span class="note-icon">
                ✓
            </span>

            <div>

                <strong>
                    Saldo aman dan tercatat
                </strong>

                <p>
                    Setiap setoran yang diterima akan otomatis
                    tercatat ke dalam saldo kamu.
                </p>

            </div>

        </div>


    </div>

</main>



<style>

/* ======================================================
   SALDO CONTAINER
====================================================== */

.saldo-container {

    max-width: 900px;

    margin-top: 25px;

}



/* ======================================================
   SALDO CARD
====================================================== */

.saldo-card {

    position: relative;

    overflow: hidden;

    padding: 30px;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #166534,
            #16a34a
        );

    color: white;

    box-shadow:
        0 12px 30px rgba(22, 101, 52, .20);

}


.saldo-card::after {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    right: -60px;

    top: -70px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);

}


.saldo-card-top {

    position: relative;

    z-index: 1;

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

}


.saldo-label {

    font-size: 11px;

    font-weight: 800;

    letter-spacing: 1.5px;

    opacity: .8;

}


.saldo-title {

    margin: 6px 0 0;

    font-size: 15px;

    opacity: .9;

}


.saldo-icon {

    position: relative;

    z-index: 2;

    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 13px;

    background: rgba(255,255,255,.15);

    font-size: 23px;

}


.saldo-value {

    position: relative;

    z-index: 1;

    margin-top: 28px;

    font-size: 36px;

    font-weight: 800;

    letter-spacing: .3px;

}


.saldo-footer {

    position: relative;

    z-index: 1;

    display: flex;

    justify-content: space-between;

    margin-top: 25px;

    padding-top: 15px;

    border-top: 1px solid rgba(255,255,255,.18);

    font-size: 12px;

    opacity: .85;

}



/* ======================================================
   ACTIONS
====================================================== */

.saldo-actions {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 15px;

    margin-top: 20px;

}


.saldo-action {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 18px;

    border-radius: 13px;

    background: white;

    color: #1f2937;

    text-decoration: none;

    box-shadow:
        0 7px 22px rgba(0,0,0,.05);

    transition: .2s;

}


.saldo-action:hover {

    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(0,0,0,.08);

}


.action-icon {

    width: 45px;

    height: 45px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #dcfce7;

    color: #166534;

    font-size: 20px;

    font-weight: 700;

}


.saldo-action strong {

    display: block;

    margin-bottom: 4px;

    font-size: 14px;

}


.saldo-action span {

    display: block;

    color: #9ca3af;

    font-size: 12px;

}


.action-arrow {

    margin-left: auto;

    color: #166534 !important;

    font-size: 18px !important;

}



/* ======================================================
   INFO CARD
====================================================== */

.info-card {

    margin-top: 25px;

    padding: 25px;

    border-radius: 15px;

    background: white;

    box-shadow:
        0 7px 22px rgba(0,0,0,.05);

}


.info-header {

    display: flex;

    align-items: center;

    gap: 12px;

    padding-bottom: 20px;

    border-bottom: 1px solid #f0f0f0;

}


.info-icon {

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: #f0fdf4;

    font-size: 19px;

}


.info-header h2 {

    margin: 0 0 4px;

    color: #166534;

    font-size: 18px;

}


.info-header p {

    margin: 0;

    color: #9ca3af;

    font-size: 12px;

}



/* ======================================================
   INFO LIST
====================================================== */

.info-list {

    margin-top: 20px;

}


.info-item {

    display: flex;

    gap: 13px;

    padding: 14px 0;

}


.info-item + .info-item {

    border-top: 1px solid #f3f4f6;

}


.info-number {

    width: 30px;

    height: 30px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #dcfce7;

    color: #166534;

    font-size: 12px;

    font-weight: 800;

}


.info-item strong {

    display: block;

    margin-bottom: 4px;

    color: #374151;

    font-size: 13px;

}


.info-item p {

    margin: 0;

    color: #6b7280;

    font-size: 12px;

    line-height: 1.5;

}



/* ======================================================
   NOTE
====================================================== */

.saldo-note {

    display: flex;

    align-items: center;

    gap: 12px;

    margin-top: 20px;

    padding: 15px;

    border-radius: 11px;

    background: #f0fdf4;

    border: 1px solid #dcfce7;

}


.note-icon {

    width: 32px;

    height: 32px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    background: #dcfce7;

    color: #166534;

    font-weight: 800;

}


.saldo-note strong {

    display: block;

    margin-bottom: 3px;

    color: #166534;

    font-size: 13px;

}


.saldo-note p {

    margin: 0;

    color: #6b7280;

    font-size: 12px;

}



/* ======================================================
   RESPONSIVE
====================================================== */

@media (max-width: 650px) {

    .saldo-container {

        margin-top: 20px;

    }


    .saldo-card {

        padding: 23px;

    }


    .saldo-value {

        font-size: 29px;

    }


    .saldo-actions {

        grid-template-columns: 1fr;

    }


    .info-card {

        padding: 20px;

    }

}


@media (max-width: 400px) {

    .saldo-card {

        padding: 20px;

    }


    .saldo-value {

        font-size: 25px;

    }


    .saldo-footer {

        flex-direction: column;

        gap: 5px;

    }

}

</style>