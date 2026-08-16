<?php

require_once __DIR__ . '/../../config/database.php';

$tanggal_dari   = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$status_filter  = $_GET['status'] ?? '';

$where_setoran = [];
$params_setoran = [];

if ($tanggal_dari !== '') {
    $where_setoran[] = "DATE(s.created_at) >= ?";
    $params_setoran[] = $tanggal_dari;
}

if ($tanggal_sampai !== '') {
    $where_setoran[] = "DATE(s.created_at) <= ?";
    $params_setoran[] = $tanggal_sampai;
}

if ($status_filter !== '') {
    $where_setoran[] = "s.status = ?";
    $params_setoran[] = $status_filter;
}

$sql_setoran = "
    SELECT
        s.id,
        s.created_at,
        u.nama,
        js.nama_sampah,
        s.berat,
        s.harga_per_kg,
        s.total_harga,
        s.status
    FROM setoran s
INNER JOIN users u
    ON s.user_id = u.id
INNER JOIN jenis_sampah js
    ON s.jenis_sampah_id = js.id
";

if (!empty($where_setoran)) {
    $sql_setoran .= " WHERE " . implode(" AND ", $where_setoran);
}

$sql_setoran .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql_setoran);
$stmt->execute($params_setoran);
$data_setoran = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ========================================
// DATA PENARIKAN
// ========================================

$stmt = $pdo->query("
    SELECT
        p.kode_penarikan,
        p.jumlah,
        p.metode,
        p.nomor_tujuan,
        p.status,
        p.tanggal_pengajuan,
        n.nama
    FROM penarikan p
    INNER JOIN nasabah n
        ON p.nasabah_id = n.id
    ORDER BY p.tanggal_pengajuan DESC
");

$data_penarikan = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Cetak Laporan Bank Sampah</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #111827;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 30px;
        }

        h2 {
            margin-top: 30px;
            color: #166534;
        }

        .filter-info {
            margin-bottom: 20px;
            padding: 12px;
            background: #f3f4f6;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #dcfce7;
            color: #166534;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .print-button {
            margin-bottom: 25px;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            background: #166534;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {

            body {
                margin: 10mm;
            }

            .print-button {
                display: none;
            }

        }

    </style>

</head>

<body>

<button
    class="print-button"
    onclick="window.print()"
>
    🖨️ Cetak Laporan
</button>


<h1>Bank Sampah</h1>

<div class="subtitle">
    Laporan Aktivitas Bank Sampah
</div>


<div class="filter-info">

    <strong>Filter:</strong>

    <?php if ($tanggal_dari !== ''): ?>

        Dari <?= htmlspecialchars($tanggal_dari) ?>

    <?php else: ?>

        Semua tanggal

    <?php endif; ?>


    <?php if ($tanggal_sampai !== ''): ?>

        sampai <?= htmlspecialchars($tanggal_sampai) ?>

    <?php endif; ?>


    <?php if ($status_filter !== ''): ?>

        | Status:
        <?= htmlspecialchars(ucfirst($status_filter)) ?>

    <?php endif; ?>

</div>


<!-- ======================================== -->
<!-- LAPORAN SETORAN -->
<!-- ======================================== -->

<h2>Laporan Transaksi Setor</h2>

<table>

    <thead>

        <tr>

            <th>No</th>
            <th>Tanggal</th>
            <th>Nasabah</th>
            <th>Jenis Sampah</th>
            <th>Berat</th>
            <th>Harga/kg</th>
            <th>Total</th>
            <th>Status</th>

        </tr>

    </thead>

    <tbody>

        <?php if (!empty($data_setoran)): ?>

            <?php $no = 1; ?>

            <?php foreach ($data_setoran as $row): ?>

                <tr>

                    <td class="text-center">
                        <?= $no++ ?>
                    </td>

                    <td>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime($row['created_at'])
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nama']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($row['nama_sampah']) ?>
                    </td>

                    <td>
                        <?= number_format(
                            $row['berat'],
                            2,
                            ',',
                            '.'
                        ) ?>
                        kg
                    </td>

                    <td>
                        Rp
                        <?= number_format(
                            $row['harga_per_kg'],
                            0,
                            ',',
                            '.'
                        ) ?>
                    </td>

                    <td>
                        <strong>
                            Rp
                            <?= number_format(
                                $row['total_harga'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            ucfirst($row['status'])
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="8"
                    class="text-center"
                >
                    Belum ada transaksi setor.
                </td>

            </tr>

        <?php endif; ?>

    </tbody>

</table>


<!-- ======================================== -->
<!-- LAPORAN PENARIKAN -->
<!-- ======================================== -->

<h2>Laporan Penarikan</h2>

<table>

    <thead>

        <tr>

            <th>No</th>
            <th>Tanggal</th>
            <th>Kode</th>
            <th>Nasabah</th>
            <th>Jumlah</th>
            <th>Metode</th>
            <th>Nomor Tujuan</th>
            <th>Status</th>

        </tr>

    </thead>

    <tbody>

        <?php if (!empty($data_penarikan)): ?>

            <?php $no = 1; ?>

            <?php foreach ($data_penarikan as $row): ?>

                <tr>

                    <td class="text-center">
                        <?= $no++ ?>
                    </td>

                    <td>
                        <?= date(
                            'd-m-Y H:i',
                            strtotime(
                                $row['tanggal_pengajuan']
                            )
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $row['kode_penarikan']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $row['nama']
                        ) ?>
                    </td>

                    <td>
                        <strong>
                            Rp
                            <?= number_format(
                                $row['jumlah'],
                                0,
                                ',',
                                '.'
                            ) ?>
                        </strong>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            ucfirst($row['metode'])
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $row['nomor_tujuan']
                        ) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            ucfirst($row['status'])
                        ) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td
                    colspan="8"
                    class="text-center"
                >
                    Belum ada data penarikan.
                </td>

            </tr>

        <?php endif; ?>

    </tbody>

</table>


</body>

</html>