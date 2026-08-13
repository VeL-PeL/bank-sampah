<?php

require_once __DIR__ . '/../config/app.php';

/**
 * Memastikan user sudah login.
 */
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit;
    }
}


/**
 * Memastikan user memiliki role tertentu.
 */
function require_role(string $role): void
{
    require_login();

    if ($_SESSION['role'] !== $role) {

        if ($_SESSION['role'] === 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: ../nasabah/dashboard.php');
        }

        exit;
    }
}


/**
 * Mengarahkan user yang sudah login
 * berdasarkan role-nya.
 */
function redirect_if_logged_in(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    }

    if ($_SESSION['role'] === 'nasabah') {
        header('Location: ../nasabah/dashboard.php');
        exit;
    }
}


/**
 * Mendapatkan ID user yang sedang login.
 */
function current_user_id(): ?int
{
    return isset($_SESSION['user_id'])
        ? (int) $_SESSION['user_id']
        : null;
}


/**
 * Mendapatkan role user yang sedang login.
 */
function current_user_role(): ?string
{
    return $_SESSION['role'] ?? null;
}


/**
 * Membuat CSRF token.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


/**
 * Memeriksa CSRF token.
 */
function verify_csrf_token(?string $token): bool
{
    if (
        empty($token) ||
        empty($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION['csrf_token'],
        $token
    );
}