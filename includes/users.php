<?php
declare(strict_types=1);

function findUserByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user === false ? null : $user;
}

function findUserById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user === false ? null : $user;
}

function addUser(string $name, string $email, string $password): int
{
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, 'student']);
    return (int) db()->lastInsertId();
}

function verifyUserPassword(array $user, string $password): bool
{
    return password_verify($password, (string) ($user['password_hash'] ?? ''));
}
