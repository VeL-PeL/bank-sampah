<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/admin-auth.php';

$setoran = [];
$error = '';

try {

    $stmt = $pdo->query("
        SELECT
            setoran.id,
            users.nama AS nama_nasabah,
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

        ORDER BY setoran.created_at DESC
    ");

    $setoran = $stmt->fetchAll();

} catch (PDOException $e) {

    $error = 'Gagal mengambil data setoran.';
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

    <title>Kelola Setoran - Bank Sampah</title>

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
            max-width: 1200px;
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

        .description {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        th {
            background: #f0fdf4;
            color: #166534;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
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

        .empty {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }

        .error {
            padding: 15px;
            border-radius: 8px;
            background: #fee2e2;
            color: #b91c1c;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 6px;
            background: #166534;
            color: white;
            text-decoration: none;
            font-size: 13px;
        }

        .btn:hover {
            background: #14532d;
        }

    </style>

</head>

<body>

<div class="container">

    <a
        href="dashboard.php"
        class="back"
    >
        ← Kembali ke Dashboard
    </a>

    <div class="card">

        <h1>Kelola Setoran</h1>

        <p class="description">
            Berikut adalah semua pengajuan setoran dari nasabah.
        </p>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php elseif (empty($setoran)): ?>

            <div class="empty">
                Belum ada pengajuan setoran.
            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>

                            <th>No</th>
                            <th>Nasabah</th>
                            <th>Jenis Sampah</th>
                            <th>Berat</th>
                            <th>Harga/kg</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($setoran as $index => $item): ?>

                        <tr>

                            <td>
                                <?= $index + 1 ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $item['nama_nasabah']
                                ) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $item['nama_sampah']
                                ) ?>
                            </td>

                            <td>
                                <?= number_format(
                                    $item['berat'],
                                    2,
                                    ',',
                                    '.'
                                ) ?>
                                kg
                            </td>

                            <td>
                                Rp
                                <?= number_format(
                                    $item['harga_per_kg'],
                                    0,
                                    ',',
                                    '.'
                                ) ?>
                            </td>

                            <td>
                                <strong>
                                    Rp
                                    <?= number_format(
                                        $item['total_harga'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                                </strong>
                            </td>

                            <td>

                                <span
                                    class="status <?= htmlspecialchars(
                                        $item['status']
                                    ) ?>"
                                >
                                    <?= ucfirst(
                                        htmlspecialchars(
                                            $item['status']
                                        )
                                    ) ?>
                                </span>

                            </td>

                            <td>
                                <?= date(
                                    'd-m-Y H:i',
                                    strtotime(
                                        $item['created_at']
                                    )
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="detail-setoran.php?id=<?= (int) $item['id'] ?>"
                                    class="btn"
                                >
                                    Detail
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>