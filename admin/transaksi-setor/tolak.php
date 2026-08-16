
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
// PROSES TOLAK
// ========================================

try {

    $pdo->beginTransaction();


    // ========================================
    // CEK TRANSAKSI
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            status
        FROM setoran
        WHERE id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([$id]);

    $setoran = $stmt->fetch(PDO::FETCH_ASSOC);


    // ========================================
    // CEK DATA
    // ========================================

    if (!$setoran) {

        throw new Exception(
            'Transaksi setor tidak ditemukan.'
        );

    }


    // ========================================
    // HANYA BOLEH MENOLAK
    // TRANSAKSI MENUNGGU
    // ========================================

    if ($setoran['status'] !== 'menunggu') {

        throw new Exception(
            'Transaksi ini sudah diproses sebelumnya.'
        );

    }


    // ========================================
    // UBAH STATUS MENJADI DITOLAK
    // ========================================

    $stmt = $pdo->prepare("
        UPDATE setoran
        SET status = 'ditolak'
        WHERE id = ?
        AND status = 'menunggu'
    ");

    $stmt->execute([$id]);


    // ========================================
    // PASTIKAN BERHASIL
    // ========================================

    if ($stmt->rowCount() !== 1) {

        throw new Exception(
            'Status transaksi gagal diperbarui.'
        );

    }


    // ========================================
    // SIMPAN PERUBAHAN
    // ========================================

    $pdo->commit();


    $_SESSION['success'] =
        'Setoran berhasil ditolak.';


} catch (Throwable $e) {


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
