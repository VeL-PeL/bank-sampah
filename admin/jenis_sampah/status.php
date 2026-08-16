
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// AMBIL ID
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: index.php');
    exit;

}


// ========================================
// AMBIL STATUS SAAT INI
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama_sampah,
            status
        FROM jenis_sampah
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $sampah = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data jenis sampah.');

}


if (!$sampah) {

    header('Location: index.php');
    exit;

}


// ========================================
// TENTUKAN STATUS BARU
// ========================================

if ($sampah['status'] === 'aktif') {

    $status_baru = 'nonaktif';

} else {

    $status_baru = 'aktif';

}


// ========================================
// UPDATE STATUS
// ========================================

try {

    $stmt = $pdo->prepare("
        UPDATE jenis_sampah
        SET status = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $status_baru,
        $id
    ]);

} catch (PDOException $e) {

    die('Gagal mengubah status jenis sampah.');

}


// ========================================
// KEMBALI
// ========================================

header(
    'Location: index.php?status=success'
);

exit;
