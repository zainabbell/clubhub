<?php
declare(strict_types=1);

function isRegistered(int $userId, int $eventId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1');
    $stmt->execute([$userId, $eventId]);
    return $stmt->fetchColumn() !== false;
}

function registerUser(int $userId, int $eventId, string $motivation): string
{
    $pdo = db();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT e.capacity, COUNT(r.id) AS registered_count
             FROM events e
             LEFT JOIN registrations r ON r.event_id = e.id
             WHERE e.id = ?
             GROUP BY e.id
             FOR UPDATE'
        );
        $stmt->execute([$eventId]);
        $row = $stmt->fetch();
        if ($row === false) {
            $pdo->rollBack();
            return 'full';
        }

        $capacity = (int) $row['capacity'];
        $registered = (int) $row['registered_count'];
        if ($registered >= $capacity) {
            $pdo->rollBack();
            return 'full';
        }

        $check = $pdo->prepare('SELECT 1 FROM registrations WHERE user_id = ? AND event_id = ? LIMIT 1 FOR UPDATE');
        $check->execute([$userId, $eventId]);
        if ($check->fetchColumn() !== false) {
            $pdo->rollBack();
            return 'already';
        }

        $ins = $pdo->prepare('INSERT INTO registrations (user_id, event_id, motivation) VALUES (?, ?, ?)');
        $ins->execute([$userId, $eventId, $motivation]);
        $pdo->commit();
        return 'ok';
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($exception->getCode() === '23000') {
            return 'already';
        }
        throw $exception;
    }
}

function cancelRegistration(int $userId, int $eventId): bool
{
    $stmt = db()->prepare('DELETE FROM registrations WHERE user_id = ? AND event_id = ?');
    $stmt->execute([$userId, $eventId]);
    return $stmt->rowCount() > 0;
}

function registrationsOfUser(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT r.id AS registration_id, r.event_id, r.motivation, r.created_at AS registered_at,
                e.title, e.club, e.location, e.starts_at, e.ends_at, e.capacity, e.description,
                (e.capacity - COUNT(r2.id)) AS places_left
         FROM registrations r
         JOIN events e ON e.id = r.event_id
         LEFT JOIN registrations r2 ON r2.event_id = e.id
         WHERE r.user_id = ?
         GROUP BY r.id, r.event_id, r.motivation, r.created_at, e.title, e.club, e.location, e.starts_at, e.ends_at, e.capacity, e.description
         ORDER BY e.starts_at ASC'
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
