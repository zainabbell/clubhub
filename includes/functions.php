<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function toDateTime(string $value): ?DateTimeImmutable
{
    try {
        return new DateTimeImmutable($value);
    } catch (Throwable) {
        return null;
    }
}

function formatDate(string $value): string
{
    $dt = toDateTime($value);
    if ($dt === null) {
        return $value;
    }

    $months = [
        1 => 'janv.', 2 => 'févr.', 3 => 'mars', 4 => 'avr.', 
        5 => 'mai', 6 => 'juin', 7 => 'juil.', 8 => 'août', 
        9 => 'sept.', 10 => 'oct.', 11 => 'nov.', 12 => 'déc.'
    ];
    $day = $dt->format('j');
    $month = $months[(int) $dt->format('n')] ?? $dt->format('m');
    return $day . ' ' . $month;
}

function formatHour(string $value): string
{
    $dt = toDateTime($value);
    if ($dt === null) {
        return $value;
    }
    $hour = (int) $dt->format('G');
    $min = $dt->format('i');
    if ($min === '00') {
        return $hour . ' h';
    }
    return $hour . ' h ' . $min;
}

function formatTimeRange(string $start, ?string $end = null): string
{
    $startLabel = formatDate($start) . ', ' . formatHour($start);
    if ($end === null || $end === '') {
        return $startLabel;
    }
    $endDt = toDateTime($end);
    $startDt = toDateTime($start);
    if ($endDt !== null && $startDt !== null && $endDt->format('Y-m-d') === $startDt->format('Y-m-d')) {
        return $startLabel . ' – ' . formatHour($end);
    }
    return $startLabel . ' – ' . formatDate($end) . ', ' . formatHour($end);
}

function isoDate(string $value): string
{
    $dt = toDateTime($value);
    return $dt ? $dt->format(DATE_ATOM) : $value;
}

function placesLabel(int $left): string
{
    if ($left <= 0) {
        return 'Complet';
    }
    if ($left === 1) {
        return '1 place restante';
    }
    return $left . ' places restantes';
}

function lines(string $text): array
{
    $text = str_replace(["\r\n", "\r"], "\n", trim($text));
    if ($text === '') {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode("\n", $text)), static fn(string $l): bool => $l !== ''));
}

function isLoggedIn(): bool
{
    return currentUserId() !== null;
}

function currentUserId(): ?int
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    $id = $_SESSION['user_id'];
    if (is_int($id)) {
        return $id;
    }
    if (is_string($id) && ctype_digit($id)) {
        return (int) $id;
    }
    return null;
}

function currentUser(): ?array
{
    $id = currentUserId();
    if ($id === null) {
        return null;
    }
    return findUserById($id);
}

function requireLogin(): void
{
    if (currentUserId() === null) {
        $target = $_SERVER['REQUEST_URI'] ?? 'mon-compte.php';
        flash('error', 'Connectez-vous pour accéder à cette page');
        header('Location: connexion.php?redirect=' . urlencode((string) $target));
        exit;
    }
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function safeNext(?string $n): string
{
    $n = $n ?? 'index.php';
    if (!preg_match('#^/[a-zA-Z0-9_./?=&-]*$#', $n) && !preg_match('#^[a-zA-Z0-9_./?=&-]+$#', $n)) {
        return 'index.php';
    }
    return $n;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $value = (string) $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}
