<?php
declare(strict_types=1);

/** HTML-escape for output. Use on EVERY dynamic value. */
function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(float|int|string|null $amount): string
{
    return '₹' . number_format((float) $amount, 2);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** One-shot messages shown via SweetAlert2 in the footer partial. */
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function flash_pull(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/** Invoice number derived from the booking id (no extra column needed). */
function invoice_no(int $bookingId): string
{
    return sprintf('DE-%06d', $bookingId % 1000000);
}

/** Inclusive-of-start whole-day count between two Y-m-d dates, minimum 1. */
function rental_days(string $start, string $end): int
{
    $d = (new DateTimeImmutable($start))->diff(new DateTimeImmutable($end))->days;
    return max(1, (int) $d);
}

/** Base URL path of /public, so links work at the domain root or in a subfolder. */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $pub  = realpath(__DIR__ . '/../public');
        $root = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $base = ($root && $pub && str_starts_with($pub, $root)) ? str_replace('\\', '/', substr($pub, strlen($root))) : '';
    }
    return $base;
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** True on HTTPS, including behind a TLS-terminating proxy/CDN (Cloudflare, load balancer). */
function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? '') === '443'
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
}

/** Public fleet-owner (admin) registration switch. Default on = original behaviour. */
function admin_signup_enabled(): bool
{
    return filter_var(config('ALLOW_ADMIN_SIGNUP', 'true'), FILTER_VALIDATE_BOOLEAN);
}
