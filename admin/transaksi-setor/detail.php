
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// AMBIL ID TRANSAKSI
// ========================================

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}


// ========================================
// AMBIL DATA TRANSAKSI
// ========================================

$transaksi = null;
$error = '';

try {

    $stmt = $pdo->prepare("
    SELECT
        s.id,
        s.user_id,
        u.nama AS nama_nasabah,
        u.email AS email_nasabah,
        js.nama_sampah,
        js.harga_per_kg AS harga_jenis_sampah,
        s.berat,
        s.harga_per_kg,
        s.total_harga,
        s.status,
        s.created_at
    FROM setoran s

    INNER JOIN users u
        ON s.user_id = u.id

    INNER JOIN jenis_sampah js
        ON s.jenis_sampah_id = js.id

    WHERE s.id = ?

    LIMIT 1
");

    $stmt->execute([$id]);

    $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaksi) {
        $error = 'Data transaksi setor tidak ditemukan.';
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil data transaksi setor.';
}


// ========================================
// HEADER & SIDEBAR
// ========================================

$page_title = 'Detail Transaksi Setor';

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

?>


<main class="main-content">

<?php if (!empty($_SESSION['success'])): ?>

    <div
        style="
            margin-top: 25px;
            padding: 15px 18px;
            background: #dcfce7;
            color: #166534;
            border-radius: 10px;
            font-weight: 600;
        "
    >

        <?= htmlspecialchars(
            $_SESSION['success']
        ) ?>

    </div>

    <?php unset($_SESSION['success']); ?>

<?php endif; ?>


<?php if (!empty($_SESSION['error'])): ?>

    <div
        style="
            margin-top: 25px;
            padding: 15px 18px;
            background: #fee2e2;
            color: #b91c1c;
            border-radius: 10px;
            font-weight: 600;
        "
    >

        <?= htmlspecialchars(
            $_SESSION['error']
        ) ?>

    </div>

    <?php unset($_SESSION['error']); ?>

<?php endif; ?>


    <!-- ========================================
         TOPBAR
    ======================================== -->

    <div class="topbar">

        <div class="topbar-left">

            <div class="page-title">

                <h1>
                    Detail Transaksi Setor
                </h1>

                <p>
                    Informasi lengkap transaksi setor sampah.
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
                            $_SESSION['nama'] ?? 'A',
                            0,
                            1
                        )
                    ) ?>

                </div>


                <div class="user-details">

                    <div class="user-name">

                        <?= htmlspecialchars(
                            $_SESSION['nama'] ?? 'Administrator'
                        ) ?>

                    </div>

                    <div class="user-role">
                        Administrator
                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- ========================================
         CONTENT
    ======================================== -->

    <div style="
        margin-top: 30px;
        max-width: 1000px;
    ">


        <?php if ($error !== ''): ?>

            <div style="
                background: #fee2e2;
                color: #b91c1c;
                padding: 20px;
                border-radius: 12px;
                margin-bottom: 20px;
            ">

                <?= htmlspecialchars($error) ?>

            </div>


            <a
                href="index.php"
                style="
                    display: inline-block;
                    padding: 10px 18px;
                    background: #166534;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                "
            >
                ← Kembali
            </a>


        <?php else: ?>


            <!-- ========================================
                 KARTU IDENTITAS TRANSAKSI
            ======================================== -->

            <div style="
                background: white;
                padding: 30px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
                margin-bottom: 20px;
            ">

                <div style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 20px;
                    flex-wrap: wrap;
                ">


                    <div style="
                        display: flex;
                        align-items: center;
                        gap: 18px;
                    ">

                        <div style="
                            width: 65px;
                            height: 65px;
                            border-radius: 50%;
                            background: #dcfce7;
                            color: #166534;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 28px;
                            font-weight: bold;
                        ">

                            <?= strtoupper(
                                substr(
                                    $transaksi['nama_nasabah'],
                                    0,
                                    1
                                )
                            ) ?>

                        </div>


                        <div>

                            <h2 style="
                                margin: 0 0 5px 0;
                                color: #166534;
                            ">

                                <?= htmlspecialchars(
                                    $transaksi['nama_nasabah']
                                ) ?>

                            </h2>


                            <div style="
                                color: #6b7280;
                            ">

                                <?= htmlspecialchars(
                                    $transaksi['email_nasabah']
                                ) ?>

                            </div>

                        </div>

                    </div>



                    <?php

                    $status = $transaksi['status'];

                    if ($status === 'menunggu') {

                        $status_background = '#fef3c7';
                        $status_color = '#92400e';
                        $status_text = 'Menunggu';

                    } elseif ($status === 'diterima') {

                        $status_background = '#dcfce7';
                        $status_color = '#166534';
                        $status_text = 'Diterima';

                    } elseif ($status === 'ditolak') {

                        $status_background = '#fee2e2';
                        $status_color = '#b91c1c';
                        $status_text = 'Ditolak';

                    } else {

                        $status_background = '#e5e7eb';
                        $status_color = '#374151';
                        $status_text = ucfirst($status);

                    }

                    ?>


                    <span style="
                        display: inline-block;
                        padding: 9px 16px;
                        border-radius: 20px;
                        background: <?= $status_background ?>;
                        color: <?= $status_color ?>;
                        font-weight: bold;
                    ">

                        <?= htmlspecialchars($status_text) ?>

                    </span>


                </div>

            </div>



            <!-- ========================================
                 DETAIL TRANSAKSI
            ======================================== -->

            <div style="
                background: white;
                padding: 30px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
                margin-bottom: 20px;
            ">


                <h2 style="
                    margin-top: 0;
                    color: #166534;
                ">
                    Informasi Setoran
                </h2>


                <div style="
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 18px;
                ">


                    <!-- ID -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            ID Transaksi
                        </div>

                        <strong>
                            #<?= (int) $transaksi['id'] ?>
                        </strong>

                    </div>



                    <!-- NASABAH -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Nasabah
                        </div>

                        <strong>
                            <?= htmlspecialchars(
                                $transaksi['nama_nasabah']
                            ) ?>
                        </strong>

                    </div>



                    <!-- JENIS SAMPAH -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Jenis Sampah
                        </div>

                        <strong>
                            <?= htmlspecialchars(
                                $transaksi['nama_sampah']
                            ) ?>
                        </strong>

                    </div>



                    <!-- BERAT -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Berat
                        </div>

                        <strong>

                            <?= number_format(
                                $transaksi['berat'],
                                2,
                                ',',
                                '.'
                            ) ?>

                            kg

                        </strong>

                    </div>



                    <!-- HARGA -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Harga per Kg
                        </div>

                        <strong>

                            Rp
                            <?= number_format(
                                $transaksi['harga_per_kg'],
                                0,
                                ',',
                                '.'
                            ) ?>

                        </strong>

                    </div>



                    <!-- TOTAL -->

                    <div style="
                        background: #f0fdf4;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #166534;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Total Harga
                        </div>

                        <strong style="
                            color: #166534;
                            font-size: 20px;
                        ">

                            Rp
                            <?= number_format(
                                $transaksi['total_harga'],
                                0,
                                ',',
                                '.'
                            ) ?>

                        </strong>

                    </div>



                    <!-- TANGGAL -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 5px;
                        ">
                            Tanggal Setor
                        </div>

                        <strong>

                            <?= date(
                                'd-m-Y H:i',
                                strtotime(
                                    $transaksi['created_at']
                                )
                            ) ?>

                        </strong>

                    </div>



                    <!-- STATUS -->

                    <div style="
                        background: #f9fafb;
                        padding: 18px;
                        border-radius: 10px;
                    ">

                        <div style="
                            color: #6b7280;
                            font-size: 14px;
                            margin-bottom: 8px;
                        ">
                            Status
                        </div>

                        <span style="
                            display: inline-block;
                            padding: 6px 12px;
                            border-radius: 20px;
                            background: <?= $status_background ?>;
                            color: <?= $status_color ?>;
                            font-weight: bold;
                        ">

                            <?= htmlspecialchars(
                                $status_text
                            ) ?>

                        </span>

                    </div>


                </div>

            </div>



            <!-- ========================================
                 AKSI
            ======================================== -->

            <div style="
                background: white;
                padding: 25px;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0,0,0,.06);
            ">

                <div style="
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                ">


                    <a
                        href="index.php"
                        style="
                            display: inline-block;
                            padding: 11px 18px;
                            background: #f3f4f6;
                            color: #374151;
                            text-decoration: none;
                            border-radius: 8px;
                            font-weight: 600;
                        "
                    >
                        ← Kembali
                    </a>


                    <?php if ($transaksi['status'] === 'menunggu'): ?>

                       <form
    method="POST"
    action="terima.php"
    style="display:inline;"
    onsubmit="
        return confirm(
            'Yakin ingin menerima setoran ini? Saldo nasabah akan bertambah.'
        );
    "
>

    <input
        type="hidden"
        name="id"
        value="<?= (int) $transaksi['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            csrf_token()
        ) ?>"
    >

    <button
        type="submit"
        style="
            display: inline-block;
            padding: 11px 18px;
            background: #166534;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        "
    >
        ✓ Terima Setoran
    </button>

</form>


                       <form
    method="POST"
    action="tolak.php"
    style="display:inline;"
    onsubmit="
        return confirm(
            'Yakin ingin menolak setoran ini?'
        );
    "
>

    <input
        type="hidden"
        name="id"
        value="<?= (int) $transaksi['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            csrf_token()
        ) ?>"
    >

    <button
        type="submit"
        style="
            display: inline-block;
            padding: 11px 18px;
            background: #dc2626;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        "
    >
        ✕ Tolak Setoran
    </button>

</form>

                    <?php endif; ?>


                </div>

            </div>


        <?php endif; ?>


    </div>


</main>


<?php

require_once __DIR__ . '/../../includes/footer.php';

?>
