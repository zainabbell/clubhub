<?php
function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function requireLogin(): void {
    if (!currentUser()) {
        flash('error', 'Connectez-vous pour accéder à cette page');
        $next = $_SERVER['REQUEST_URI'] ?? 'mon-compte.php';
        redirect('connexion.php?next=' . urlencode($next));
    }
}
function loginUser(array $u): void { 
    $_SESSION['user'] = [
        'id' => $u['id'],
        'name' => $u['name'],
        'email' => $u['email'],
        'role' => $u['role']
    ]; 
}
function logoutUser(): void { unset($_SESSION['user']); }
function redirect(string $to): void { header('Location: ' . $to); exit; }
function safeNext(?string $n): string { 
    $n = $n ?? 'index.php'; 
    if (!preg_match('#^/[a-zA-Z0-9_./?=&-]*$#', $n) && !preg_match('#^[a-zA-Z0-9_./?=&-]+$#', $n)) {
        return 'index.php'; 
    }
    return $n; 
}
function flash(string $type, string $msg): void { $_SESSION['flash'] = ['type' => $type, 'msg' => $msg]; }
function takeFlash(): ?array { 
    $f = $_SESSION['flash'] ?? null; 
    unset($_SESSION['flash']); 
    return $f; 
}
