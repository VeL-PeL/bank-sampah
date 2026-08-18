
<?php

require_once __DIR__ . '/../../includes/auth.php';

require_role('admin');

require_once __DIR__ . '/../../config/database.php';


// ========================================
// VALIDASI REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}


$id = (int) ($_POST['id'] ?? 0);
$aksi = $_POST['aksi'] ?? '';


// ========================================
// VALIDASI ID DAN AKSI
// ========================================

if ($id <= 0) {

    header('Location: index.php?error=id_tidak_valid');
    exit;

}

if (!in_array($aksi, ['terima', 'tolak'], true)) {

    header('Location: index.php?error=aksi_tidak_valid');
    exit;

}


try {

    // ========================================
    // MULAI TRANSACTION
    // ========================================

    $pdo->beginTransaction();


    // ========================================
    // AMBIL DATA PENARIKAN
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            nasabah_id,
            jumlah,
            status
        FROM penarikan
        WHERE id = :id
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        'id' => $id
    ]);

    $penarikan = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$penarikan) {

        $pdo->rollBack();

        header('Location: index.php?error=data_tidak_ditemukan');
        exit;

    }


    // ========================================
    // HANYA PENARIKAN PENDING
    // YANG BOLEH DIPROSES
    // ========================================

    if ($penarikan['status'] !== 'pending') {

        $pdo->rollBack();

        header('Location: detail.php?id=' . $id . '&error=sudah_diproses');
        exit;

    }


    // ========================================
    // JIKA ADMIN MENOLAK
    // ========================================

    if ($aksi === 'tolak') {

        $stmt = $pdo->prepare("
            UPDATE penarikan
            SET
                status = 'ditolak',
                tanggal_diproses = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id
        ]);

        $pdo->commit();

        header('Location: detail.php?id=' . $id . '&success=ditolak');
        exit;

    }


    // ========================================
    // JIKA ADMIN MENERIMA
    // ========================================

    $nasabahId = (int) $penarikan['nasabah_id'];
    $jumlah = (float) $penarikan['jumlah'];


    // ========================================
    // AMBIL SALDO NASABAH
    // ========================================

    $stmt = $pdo->prepare("
        SELECT
            id,
            saldo
        FROM saldo
        WHERE nasabah_id = :nasabah_id
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        'nasabah_id' => $nasabahId
    ]);

    $saldoData = $stmt->fetch(PDO::FETCH_ASSOC);


    // ========================================
    // CEK DATA SALDO
    // ========================================

    if (!$saldoData) {

        $pdo->rollBack();

        header('Location: detail.php?id=' . $id . '&error=saldo_tidak_ditemukan');
        exit;

    }


    $saldoSekarang = (float) $saldoData['saldo'];


    // ========================================
    // CEK SALDO CUKUP
    // ========================================

    if ($saldoSekarang < $jumlah) {

        $pdo->rollBack();

        header('Location: detail.php?id=' . $id . '&error=saldo_tidak_cukup');
        exit;

    }


    // ========================================
    // KURANGI SALDO
    // ========================================

    $saldoBaru = $saldoSekarang - $jumlah;


    $stmt = $pdo->prepare("
        UPDATE saldo
        SET
            saldo = :saldo
        WHERE nasabah_id = :nasabah_id
    ");

    $stmt->execute([
        'saldo' => $saldoBaru,
        'nasabah_id' => $nasabahId
    ]);


    // ========================================
    // UPDATE STATUS PENARIKAN
    // ========================================

    $stmt = $pdo->prepare("
        UPDATE penarikan
        SET
            status = 'diterima',
            tanggal_diproses = NOW()
        WHERE id = :id
    ");

    $stmt->execute([
        'id' => $id
    ]);


    // ========================================
    // SIMPAN SEMUA PERUBAHAN
    // ========================================

    $pdo->commit();


    // ========================================
    // KEMBALI KE DETAIL
    // ========================================

    header('Location: detail.php?id=' . $id . '&success=diterima');
    exit;


} catch (PDOException $e) {

    // ========================================
    // BATALKAN TRANSACTION JIKA ERROR
    // ========================================

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    header('Location: detail.php?id=' . $id . '&error=gagal_memproses');
    exit;

}
