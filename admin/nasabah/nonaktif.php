
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// AMBIL ID NASABAH
// ========================================

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {

    header('Location: index.php');
    exit;

}


// ========================================
// CEK NASABAH
// ========================================

try {

    $stmt = $pdo->prepare("
        SELECT
            id,
            nama,
            status
        FROM users
        WHERE id = ?
          AND role = 'nasabah'
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $nasabah = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die('Gagal mengambil data nasabah.');

}


if (!$nasabah) {

    header('Location: index.php');
    exit;

}


// ========================================
// JIKA SUDAH NONAKTIF
// ========================================

if ($nasabah['status'] === 'nonaktif') {

    header('Location: index.php');
    exit;

}


// ========================================
// PROSES NONAKTIFKAN
// ========================================

try {

    $stmt = $pdo->prepare("
        UPDATE users
        SET status = 'nonaktif'
        WHERE id = ?
          AND role = 'nasabah'
    ");

    $stmt->execute([$id]);

} catch (PDOException $e) {

    die('Gagal menonaktifkan nasabah.');

}


// ========================================
// KEMBALI KE HALAMAN NASABAH
// ========================================

header('Location: index.php?status=nonaktif');
exit;
