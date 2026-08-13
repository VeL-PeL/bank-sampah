<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/nasabah-auth.php';

$userId = $_SESSION['user_id'];

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: riwayat.php');
    exit;
}

$setoran = null;
$error = '';

try {

    $stmt = $pdo->prepare("
        SELECT
            setoran.id,
            jenis_sampah.nama_sampah,
            setoran.berat,
            setoran.harga_per_kg,
            setoran.total_harga,
            setoran.status,
            setoran.created_at
        FROM setoran
        INNER JOIN jenis_sampah
            ON setoran.jenis_sampah_id = jenis_sampah.id
        WHERE setoran.id = :id
        AND setoran.user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        'id' => $id,
        'user_id' => $userId
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
            max-width: 700px;
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

        .detail {
            display: flex;
            justify-content: space-between;
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

        .status {
            display: inline-block;
            padding: 6px 12px;
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

        .error {
            padding: 15px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
        }

        .total {
            font-size: 20px;
            color: #166534;
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
                gap: 5px;
            }

            .value {
                text-align: left;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="riwayat.php"
        class="back"
    >
        ← Kembali ke Riwayat
    </a>

    <div class="card">

        <h1>Detail Setoran</h1>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php else: ?>

            <div class="detail">

                <span class="label">
                    Jenis Sampah
                </span>

                <span class="value">
                    <?= htmlspecialchars($setoran['nama_sampah']) ?>
                </span>

            </div>

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

            <div class="detail">

                <span class="label">
                    Status
                </span>

                <span class="value">

                    <span
                        class="status <?= htmlspecialchars($setoran['status']) ?>"
                    >
                        <?= ucfirst(
                            htmlspecialchars($setoran['status'])
                        ) ?>
                    </span>

                </span>

            </div>

            <div class="detail">

                <span class="label">
                    Tanggal
                </span>

                <span class="value">
                    <?= date(
                        'd-m-Y H:i',
                        strtotime($setoran['created_at'])
                    ) ?>
                </span>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>