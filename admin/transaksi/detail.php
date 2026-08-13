<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/admin-auth.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$setoran = null;
$error = '';

try {

    $stmt = $pdo->prepare("
        SELECT
            setoran.id,
            users.nama AS nama_nasabah,
            users.email,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.harga_per_kg,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran

        INNER JOIN users
            ON setoran.user_id = users.id

        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id

        WHERE setoran.id = :id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $setoran = $stmt->fetch();

    if (!$setoran) {
        $error = 'Data setoran tidak ditemukan.';
    }

} catch (PDOException $e) {

    $error = 'Gagal mengambil detail setoran.';
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Detail Setoran - Bank Sampah</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f0fdf4;
            color: #1f2937;
        }

        .container {
            max-width: 750px;
            margin: 40px auto;
            padding: 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #166534;
            text-decoration: none;
            font-weight: 500;
        }

        .back:hover {
            text-decoration: underline;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
        }

        h1 {
            margin-top: 0;
            color: #166534;
        }

        .description {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail:last-child {
            border-bottom: none;
        }

        .label {
            color: #6b7280;
        }

        .value {
            font-weight: bold;
            text-align: right;
        }

        .total {
            color: #166534;
            font-size: 20px;
        }

        /* STATUS */

        .status {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .menunggu {
            background: #fef3c7;
            color: #92400e;
        }

        .diterima {
            background: #dcfce7;
            color: #166534;
        }

        .ditolak {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* ACTION */

        .action-title {
            margin-top: 30px;
            margin-bottom: 12px;
            font-weight: bold;
            color: #374151;
        }

        .action {
            display: flex;
            gap: 12px;
            margin-top: 15px;
        }

        .action form {
            flex: 1;
            margin: 0;
        }

        .btn {
            width: 100%;
            padding: 13px 18px;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .btn-terima {
            background: #16a34a;
        }

        .btn-terima:hover {
            background: #15803d;
        }

        .btn-tolak {
            background: #dc2626;
        }

        .btn-tolak:hover {
            background: #b91c1c;
        }

        .message {
            margin-bottom: 20px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            font-weight: 500;
        }

        .error {
            padding: 15px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
        }

        .waiting-box {
            margin-top: 25px;
            padding: 15px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            color: #92400e;
        }

        .success-box {
            margin-top: 25px;
            padding: 15px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            color: #166534;
        }

        .danger-box {
            margin-top: 25px;
            padding: 15px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #b91c1c;
        }

        @media (max-width: 600px) {

            .container {
                margin: 20px auto;
                padding: 15px;
            }

            .card {
                padding: 20px;
            }

            .detail {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .value {
                text-align: left;
            }

            .action {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="index.php"
        class="back"
    >
        ← Kembali ke Transaksi
    </a>

    <div class="card">

        <h1>Detail Setoran</h1>

        <?php if (isset($_GET['success'])): ?>

            <div class="message">
                ✓ Status setoran berhasil diperbarui.
            </div>

        <?php endif; ?>


        <p class="description">
            Informasi lengkap pengajuan setoran nasabah.
        </p>


        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php else: ?>


            <!-- NAMA NASABAH -->

            <div class="detail">

                <span class="label">
                    Nama Nasabah
                </span>

                <span class="value">
                    <?= htmlspecialchars(
                        $setoran['nama_nasabah']
                    ) ?>
                </span>

            </div>


            <!-- EMAIL -->

            <div class="detail">

                <span class="label">
                    Email
                </span>

                <span class="value">
                    <?= htmlspecialchars(
                        $setoran['email']
                    ) ?>
                </span>

            </div>


            <!-- JENIS SAMPAH -->

            <div class="detail">

                <span class="label">
                    Jenis Sampah
                </span>

                <span class="value">
                    <?= htmlspecialchars(
                        $setoran['nama_sampah']
                    ) ?>
                </span>

            </div>


            <!-- BERAT -->

            <div class="detail">

                <span class="label">
                    Berat
                </span>

                <span class="value">
                    <?= number_format(
                        $setoran['berat'],
                        2,
                        ',',
                        '.'
                    ) ?>
                    kg
                </span>

            </div>


            <!-- HARGA -->

            <div class="detail">

                <span class="label">
                    Harga per Kg
                </span>

                <span class="value">
                    Rp
                    <?= number_format(
                        $setoran['harga_per_kg'],
                        0,
                        ',',
                        '.'
                    ) ?>
                </span>

            </div>


            <!-- TOTAL -->

            <div class="detail">

                <span class="label">
                    Total Harga
                </span>

                <span class="value total">
                    Rp
                    <?= number_format(
                        $setoran['total_harga'],
                        0,
                        ',',
                        '.'
                    ) ?>
                </span>

            </div>


            <!-- STATUS -->

            <div class="detail">

                <span class="label">
                    Status
                </span>

                <span class="value">

                    <span
                        class="status <?= htmlspecialchars(
                            $setoran['status']
                        ) ?>"
                    >
                        <?= ucfirst(
                            htmlspecialchars(
                                $setoran['status']
                            )
                        ) ?>
                    </span>

                </span>

            </div>


            <!-- TANGGAL -->

            <div class="detail">

                <span class="label">
                    Tanggal Pengajuan
                </span>

                <span class="value">
                    <?= date(
                        'd-m-Y H:i',
                        strtotime(
                            $setoran['created_at']
                        )
                    ) ?>
                </span>

            </div>


            <!-- AKSI ADMIN -->

            <?php if ($setoran['status'] === 'menunggu'): ?>

                <div class="action-title">
                    Aksi Setoran
                </div>

                <div class="action">

                    <!-- TERIMA -->

                    <form
                        method="POST"
                        action="proses.php"
                        onsubmit="return confirm('Yakin ingin menerima setoran ini?')"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                csrf_token()
                            ) ?>"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $setoran['id'] ?>"
                        >

                        <input
                            type="hidden"
                            name="status"
                            value="diterima"
                        >

                        <button
                            type="submit"
                            class="btn btn-terima"
                        >
                            ✓ Terima Setoran
                        </button>

                    </form>


                    <!-- TOLAK -->

                    <form
                        method="POST"
                        action="proses.php"
                        onsubmit="return confirm('Yakin ingin menolak setoran ini?')"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars(
                                csrf_token()
                            ) ?>"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int) $setoran['id'] ?>"
                        >

                        <input
                            type="hidden"
                            name="status"
                            value="ditolak"
                        >

                        <button
                            type="submit"
                            class="btn btn-tolak"
                        >
                            ✕ Tolak Setoran
                        </button>

                    </form>

                </div>


            <?php elseif ($setoran['status'] === 'diterima'): ?>

                <div class="success-box">
                    ✓ Setoran ini sudah diterima.
                </div>


            <?php elseif ($setoran['status'] === 'ditolak'): ?>

                <div class="danger-box">
                    ✕ Setoran ini sudah ditolak.
                </div>

            <?php endif; ?>


        <?php endif; ?>

    </div>

</div>

</body>

</html>