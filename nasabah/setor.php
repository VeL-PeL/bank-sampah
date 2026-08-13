<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/nasabah-auth.php';


// ======================================================
// CEK LOGIN
// ======================================================

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];


// ======================================================
// CSRF
// ======================================================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];


// ======================================================
// VARIABLE
// ======================================================

$error = '';
$success = '';
$jenisSampah = [];


// ======================================================
// AMBIL JENIS SAMPAH
// ======================================================

try {

    $stmt = $pdo->query("
        SELECT
            id,
            nama_sampah,
            harga_per_kg
        FROM jenis_sampah
        WHERE status = 'aktif'
        ORDER BY nama_sampah ASC
    ");

    $jenisSampah = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = 'Gagal mengambil data jenis sampah.';
}


// ======================================================
// PROSES FORM
// ======================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $token)) {

        $error = 'Permintaan tidak valid. Silakan coba lagi.';

    } else {

        $jenisSampahId = (int) ($_POST['jenis_sampah_id'] ?? 0);
        $berat = (float) ($_POST['berat'] ?? 0);


        // ==============================================
        // VALIDASI
        // ==============================================

        if ($jenisSampahId <= 0) {

            $error = 'Silakan pilih jenis sampah.';

        } elseif ($berat <= 0) {

            $error = 'Berat sampah harus lebih dari 0 kg.';

        } else {

            try {

                // ==========================================
                // AMBIL DATA SAMPAH
                // ==========================================

                $stmt = $pdo->prepare("
                    SELECT
                        id,
                        nama_sampah,
                        harga_per_kg
                    FROM jenis_sampah
                    WHERE id = :id
                    AND status = 'aktif'
                    LIMIT 1
                ");

                $stmt->execute([
                    'id' => $jenisSampahId
                ]);

                $sampah = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$sampah) {

                    $error = 'Jenis sampah tidak ditemukan.';

                } else {

                    // ======================================
                    // HITUNG TOTAL
                    // ======================================

                    $hargaPerKg = (float) $sampah['harga_per_kg'];

                    $totalHarga = $berat * $hargaPerKg;


                    // ======================================
                    // SIMPAN SETORAN
                    // ======================================

                    $stmt = $pdo->prepare("
                        INSERT INTO setoran (
                            user_id,
                            jenis_sampah_id,
                            berat,
                            harga_per_kg,
                            total_harga,
                            status
                        )
                        VALUES (
                            :user_id,
                            :jenis_sampah_id,
                            :berat,
                            :harga_per_kg,
                            :total_harga,
                            'menunggu'
                        )
                    ");

                    $stmt->execute([
                        'user_id' => $userId,
                        'jenis_sampah_id' => $jenisSampahId,
                        'berat' => $berat,
                        'harga_per_kg' => $hargaPerKg,
                        'total_harga' => $totalHarga
                    ]);


                    $success = 'Pengajuan setoran berhasil dikirim.';
                }

            } catch (PDOException $e) {

                $error = 'Gagal menyimpan pengajuan setoran.';

                // Untuk melihat error database sementara:
                // $error = $e->getMessage();
            }
        }
    }
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

    <title>Setor Sampah - Bank Sampah</title>


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
            width: 100%;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
        }


        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #166534;
            text-decoration: none;
            font-size: 16px;
        }


        .back:hover {
            text-decoration: underline;
        }


        .card {
            background: #ffffff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }


        h1 {
            margin: 0 0 10px;
            color: #166534;
            font-size: 32px;
        }


        .description {
            color: #6b7280;
            margin: 0 0 30px;
            line-height: 1.6;
        }


        .alert {
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }


        .error {
            background: #fee2e2;
            color: #b91c1c;
        }


        .success {
            background: #dcfce7;
            color: #166534;
        }


        .empty {
            padding: 14px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 8px;
            margin-bottom: 20px;
        }


        .form-group {
            margin-bottom: 22px;
        }


        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #374151;
        }


        select,
        input[type="number"] {
            display: block;
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            background: #ffffff;
            color: #1f2937;
            font-size: 15px;
        }


        select:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
        }


        .btn {
            display: block;
            width: 100%;
            height: 48px;
            border: none;
            border-radius: 8px;
            background: #16a34a;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }


        .btn:hover {
            background: #15803d;
        }


        @media (max-width: 600px) {

            .container {
                margin: 20px auto;
                padding: 16px;
            }

            .card {
                padding: 24px 20px;
            }

            h1 {
                font-size: 28px;
            }

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


        <h1>
            Setor Sampah
        </h1>


        <p class="description">
            Ajukan setoran sampah kamu melalui form berikut.
        </p>


        <?php if ($error !== ''): ?>

            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ''): ?>

            <div class="alert success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <?php if (empty($jenisSampah)): ?>

            <div class="empty">
                Belum ada jenis sampah aktif.
                Silakan tambahkan jenis sampah terlebih dahulu.
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >


            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($csrfToken) ?>"
            >


            <!-- JENIS SAMPAH -->

            <div class="form-group">

                <label for="jenis_sampah">
                    Jenis Sampah
                </label>


                <select
                    name="jenis_sampah_id"
                    id="jenis_sampah"
                    required
                >

                    <option value="">
                        -- Pilih Jenis Sampah --
                    </option>


                    <?php foreach ($jenisSampah as $sampah): ?>

                        <option
                            value="<?= (int) $sampah['id'] ?>"
                        >

                            <?= htmlspecialchars($sampah['nama_sampah']) ?>

                            -

                            Rp <?= number_format(
                                (float) $sampah['harga_per_kg'],
                                0,
                                ',',
                                '.'
                            ) ?>/kg

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>


            <!-- BERAT -->

            <div class="form-group">

                <label for="berat">
                    Berat Sampah (kg)
                </label>


                <input
                    type="number"
                    name="berat"
                    id="berat"
                    min="0.01"
                    step="0.01"
                    placeholder="Contoh: 2.5"
                    required
                >

            </div>


            <!-- BUTTON -->

            <button
                type="submit"
                class="btn"
            >
                Ajukan Setoran
            </button>


        </form>


    </div>


</div>


</body>

</html>