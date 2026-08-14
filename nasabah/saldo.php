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


    <!-- TOPBAR -->
    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Saldo Saya
                </h1>

                <p>
                    Lihat saldo tabungan Bank Sampah kamu.
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


    <!-- CONTENT -->
    <div class="dashboard-content">


        <!-- SALDO CARD -->
        <div
            style="
                background: linear-gradient(
                    135deg,
                    #087f5b,
                    #12b886
                );
                border-radius: 20px;
                padding: 35px;
                color: white;
                max-width: 600px;
                margin-top: 30px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            "
        >

            <p
                style="
                    margin: 0;
                    font-size: 16px;
                    opacity: 0.9;
                "
            >
                Saldo Saat Ini
            </p>


            <h2
                style="
                    margin: 12px 0 5px;
                    font-size: 36px;
                "
            >

                Rp <?= number_format(
                    $saldo,
                    0,
                    ',',
                    '.'
                ) ?>

            </h2>


            <p
                style="
                    margin: 0;
                    opacity: 0.85;
                "
            >
                Saldo tabungan Bank Sampah
            </p>

        </div>


        <!-- INFO -->
        <div
            style="
                margin-top: 25px;
                background: white;
                border-radius: 15px;
                padding: 25px;
                max-width: 600px;
            "
        >

            <h3
                style="
                    margin-top: 0;
                    color: #166534;
                "
            >
                Informasi Saldo
            </h3>


            <p
                style="
                    color: #64748b;
                    line-height: 1.6;
                "
            >
                Saldo akan bertambah setelah setoran sampah
                kamu diterima dan diproses oleh admin.
            </p>

            <p
                style="
                    color: #64748b;
                    line-height: 1.6;
                "
            >
                Kamu dapat menggunakan saldo untuk melakukan
                pengajuan penarikan.
            </p>

        </div>


    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>