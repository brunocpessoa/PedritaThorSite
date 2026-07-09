<?php
declare(strict_types=1);

session_start();

define('ADMIN_DATA_DIR', __DIR__ . '/../_data');
define('ADMIN_CREDENTIALS_FILE', ADMIN_DATA_DIR . '/credentials.json');

function admin_credentials_exist(): bool
{
    return is_file(ADMIN_CREDENTIALS_FILE);
}

function admin_load_credentials(): ?array
{
    if (!admin_credentials_exist()) {
        return null;
    }
    $raw = file_get_contents(ADMIN_CREDENTIALS_FILE);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function admin_save_credentials(array $data): void
{
    if (!is_dir(ADMIN_DATA_DIR)) {
        mkdir(ADMIN_DATA_DIR, 0750, true);
    }
    file_put_contents(ADMIN_CREDENTIALS_FILE, json_encode($data, JSON_PRETTY_PRINT));
    chmod(ADMIN_CREDENTIALS_FILE, 0640);
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function admin_csrf_check(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function admin_e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
