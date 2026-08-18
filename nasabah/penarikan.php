
<?php

require_once __DIR__ . '/../includes/auth.php';

require_role('nasabah');

require_once __DIR__ . '/../config/database.php';

$page_title = 'Penarikan Saldo';

$userId = $_SESSION['user_id'];

$saldo = 0;
$error = '';
$success = '';


// ========================================
// AMBIL SALDO NASABAH
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT s.saldo
        FROM saldo s
        INNER JOIN nasabah n
            ON n.id = s.nasabah_id
        WHERE n.user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'user_id' => $userId
    ]);

    $dataSaldo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dataSaldo) {
        $saldo = (float) $dataSaldo['saldo'];
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data saldo.';

}


// ========================================
// PROSES PENARIKAN
// ========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jumlah = (float) ($_POST['jumlah'] ?? 0);
    $metode = $_POST['metode'] ?? '';
    $nomorTujuan = trim($_POST['nomor_tujuan'] ?? '');

    /*
     * Metode harus sesuai dengan ENUM database:
     * bank
     * e_wallet
     */

    if ($jumlah <= 0) {

        $error = 'Jumlah penarikan harus lebih dari Rp 0.';

    } elseif ($jumlah > $saldo) {

        $error = 'Saldo kamu tidak mencukupi.';

    } elseif (!in_array($metode, ['bank', 'e_wallet'], true)) {

        $error = 'Metode penarikan tidak valid.';

    } elseif ($nomorTujuan === '') {

        $error = 'Nomor tujuan harus diisi.';

    } else {

        try {

            // Cari nasabah berdasarkan user yang sedang login

            $stmt = $pdo->prepare("
                SELECT id
                FROM nasabah
                WHERE user_id = :user_id
                LIMIT 1
            ");

            $stmt->execute([
                'user_id' => $userId
            ]);

            $nasabah = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$nasabah) {

                $error = 'Data nasabah tidak ditemukan.';

            } else {

                $nasabahId = (int) $nasabah['id'];


                // Buat kode penarikan

                $kodePenarikan =
                    'WD-' .
                    date('YmdHis') .
                    '-' .
                    strtoupper(
                        substr(
                            bin2hex(random_bytes(3)),
                            0,
                            6
                        )
                    );


                // Simpan pengajuan penarikan

                $stmt = $pdo->prepare("
                    INSERT INTO penarikan (
                        kode_penarikan,
                        nasabah_id,
                        jumlah,
                        metode,
                        nomor_tujuan,
                        status
                    )
                    VALUES (
                        :kode_penarikan,
                        :nasabah_id,
                        :jumlah,
                        :metode,
                        :nomor_tujuan,
                        'pending'
                    )
                ");

                $stmt->execute([
                    'kode_penarikan' => $kodePenarikan,
                    'nasabah_id' => $nasabahId,
                    'jumlah' => $jumlah,
                    'metode' => $metode,
                    'nomor_tujuan' => $nomorTujuan
                ]);


                $success =
                    'Pengajuan penarikan berhasil dikirim. ' .
                    'Kode: ' .
                    $kodePenarikan;
            }

        } catch (PDOException $e) {

            $error = 'Gagal menyimpan pengajuan penarikan.';

        }

    }

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
                    Penarikan
                </h1>

                <p>
                    Ajukan penarikan saldo Bank Sampah.
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

    <div
        style="
            max-width: 700px;
            margin-top: 30px;
        "
    >


        <!-- SALDO -->

        <div
            style="
                background: #166534;
                color: white;
                padding: 25px;
                border-radius: 15px;
                margin-bottom: 25px;
            "
        >

            <div
                style="
                    opacity: .85;
                    margin-bottom: 8px;
                "
            >
                Saldo Tersedia
            </div>

            <div
                style="
                    font-size: 32px;
                    font-weight: bold;
                "
            >

                Rp <?= number_format(
                    $saldo,
                    0,
                    ',',
                    '.'
                ) ?>

            </div>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ''): ?>

            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>


        <!-- SUCCESS -->

        <?php if ($success !== ''): ?>

            <div
                style="
                    background: #dcfce7;
                    color: #166534;
                    padding: 15px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                "
            >

                <?= htmlspecialchars($success) ?>

            </div>

        <?php endif; ?>


        <!-- FORM -->

        <div
            style="
                background: white;
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,.08);
            "
        >

            <h2
                style="
                    margin-top: 0;
                    color: #166534;
                "
            >
                Ajukan Penarikan
            </h2>


            <p
                style="
                    color: #6b7280;
                    margin-bottom: 25px;
                "
            >
                Isi data berikut untuk mengajukan penarikan saldo.
            </p>


            <form method="POST">


                <!-- JUMLAH -->

                <div style="margin-bottom: 20px;">

                    <label
                        for="jumlah"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Jumlah Penarikan
                    </label>

                    <input
                        type="number"
                        name="jumlah"
                        id="jumlah"
                        min="1"
                        max="<?= htmlspecialchars($saldo) ?>"
                        step="1"
                        placeholder="Contoh: 10000"
                        required
                        style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- METODE -->

                <div style="margin-bottom: 20px;">

                    <label
                        for="metode"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Metode Penarikan
                    </label>

                    <select
                        name="metode"
                        id="metode"
                        required
                        style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                        <option value="">
                            -- Pilih Metode --
                        </option>

                        <option value="bank">
                            Bank
                        </option>

                        <option value="e_wallet">
                            E-Wallet
                        </option>

                    </select>

                </div>


                <!-- NOMOR TUJUAN -->

                <div style="margin-bottom: 25px;">

                    <label
                        for="nomor_tujuan"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: bold;
                        "
                    >
                        Nomor Rekening / E-Wallet
                    </label>

                    <input
                        type="text"
                        name="nomor_tujuan"
                        id="nomor_tujuan"
                        placeholder="Contoh: 081234567890"
                        required
                        style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    style="
                        width: 100%;
                        padding: 13px;
                        border: none;
                        border-radius: 8px;
                        background: #16a34a;
                        color: white;
                        font-size: 16px;
                        font-weight: bold;
                        cursor: pointer;
                    "
                >
                    Ajukan Penarikan
                </button>


            </form>

        </div>

    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>
