
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth.php';
require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// HARUS MENGGUNAKAN POST
// ========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}


// ========================================
// AMBIL ID TRANSAKSI
// ========================================

$id = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($id <= 0) {

    $_SESSION['error'] =
        'ID transaksi tidak valid.';

    header('Location: index.php');
    exit;

}


// ========================================
// CEK CSRF
// ========================================

$csrf_token = $_POST['csrf_token'] ?? '';

if (!verify_csrf_token($csrf_token)) {

    $_SESSION['error'] =
        'Token keamanan tidak valid.';

    header(
        'Location: detail.php?id=' . $id
    );

    exit;

}


// ========================================
// MULAI TRANSAKSI DATABASE
// ========================================

try {

    $pdo->beginTransaction();


    // ========================================
    // 1. AMBIL DATA SETORAN
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            user_id,
            total_harga,
            status
        FROM setoran
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $setoran = $stmt->fetch(PDO::FETCH_ASSOC);


    // ========================================
    // CEK TRANSAKSI
    // ========================================

    if (!$setoran) {

        throw new Exception(
            'Transaksi setor tidak ditemukan.'
        );

    }


    // ========================================
    // 2. PASTIKAN STATUS MASIH MENUNGGU
    // ========================================

    if ($setoran['status'] !== 'menunggu') {

        throw new Exception(
            'Transaksi ini sudah diproses sebelumnya.'
        );

    }


    $user_id = (int) $setoran['user_id'];

    $total_harga = (float) $setoran['total_harga'];


    // ========================================
    // 3. CARI DATA NASABAH
    //    users.id → nasabah.user_id
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            user_id,
            nama
        FROM nasabah
        WHERE user_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$user_id]);

    $nasabah = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$nasabah) {

        throw new Exception(
            'Data nasabah untuk transaksi ini tidak ditemukan.'
        );

    }


    // ========================================
    // ID YANG BENAR UNTUK TABEL SALDO
    // ========================================

    $nasabah_id = (int) $nasabah['id'];


    // ========================================
    // 4. CEK SALDO NASABAH
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            nasabah_id,
            saldo
        FROM saldo
        WHERE nasabah_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$nasabah_id]);

    $saldo_data = $stmt->fetch(PDO::FETCH_ASSOC);


    // ========================================
    // 5. JIKA BELUM ADA SALDO
    //    BUAT SALDO BARU
    // ========================================

    if (!$saldo_data) {

        $stmt = $pdo->prepare("
            INSERT INTO saldo
            (
                nasabah_id,
                saldo
            )
            VALUES
            (
                ?,
                ?
            )
        ");

        $stmt->execute([
            $nasabah_id,
            $total_harga
        ]);


    } else {


        // ========================================
        // 6. JIKA SUDAH ADA SALDO
        //    TAMBAHKAN TOTAL SETORAN
        // ========================================

        $saldo_lama =
            (float) $saldo_data['saldo'];

        $saldo_baru =
            $saldo_lama + $total_harga;


        $stmt = $pdo->prepare("
            UPDATE saldo
            SET saldo = ?
            WHERE nasabah_id = ?
        ");

        $stmt->execute([
            $saldo_baru,
            $nasabah_id
        ]);

    }


    // ========================================
    // 7. UBAH STATUS SETORAN
    // ========================================

    $stmt = $pdo->prepare("
        UPDATE setoran
        SET status = 'diterima'
        WHERE id = ?
        AND status = 'menunggu'
    ");

    $stmt->execute([$id]);


    // ========================================
    // PASTIKAN STATUS BERHASIL DIUBAH
    // ========================================

    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            'Status transaksi gagal diperbarui.'
        );

    }


    // ========================================
    // 8. SIMPAN SEMUA PERUBAHAN
    // ========================================

    $pdo->commit();


    // ========================================
    // PESAN SUKSES
    // ========================================

    $_SESSION['success'] =
        'Setoran berhasil diterima. Saldo '
        . htmlspecialchars($nasabah['nama'])
        . ' berhasil ditambahkan sebesar Rp '
        . number_format(
            $total_harga,
            0,
            ',',
            '.'
        )
        . '.';


} catch (Throwable $e) {


    // ========================================
    // BATALKAN SEMUA PERUBAHAN JIKA ERROR
    // ========================================

    if ($pdo->inTransaction()) {

        $pdo->rollBack();

    }


    $_SESSION['error'] =
        $e->getMessage();

}


// ========================================
// KEMBALI KE DETAIL
// ========================================

header(
    'Location: detail.php?id=' . $id
);

exit;

