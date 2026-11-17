<?php
declare(strict_types=1);

function getEvents(?int $limit = null): array
{
    $sql = 'SELECT e.*, (e.capacity - COUNT(r.id)) AS places_left
            FROM events e
            LEFT JOIN registrations r ON r.event_id = e.id
            WHERE e.starts_at >= NOW()
            GROUP BY e.id
            ORDER BY e.starts_at ASC';
    if ($limit !== null) {
        $sql .= ' LIMIT ?';
        $stmt = db()->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    $stmt = db()->query($sql);
    return $stmt->fetchAll();
}

function findEvent(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT e.*, (e.capacity - COUNT(r.id)) AS places_left
         FROM events e
         LEFT JOIN registrations r ON r.event_id = e.id
         WHERE e.id = ?
         GROUP BY e.id'
    );
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    return $event === false ? null : $event;
}

function getClubs(): array
{
    $stmt = db()->query('SELECT DISTINCT club FROM events WHERE starts_at >= NOW() ORDER BY club ASC');
    $clubs = [];
    foreach ($stmt->fetchAll() as $row) {
        $club = (string) ($row['club'] ?? '');
        if ($club !== '') {
            $clubs[] = $club;
        }
    }
    return $clubs;
}

function searchEvents(string $q): array
{
    $like = '%' . $q . '%';
    $stmt = db()->prepare(
        'SELECT e.*, (e.capacity - COUNT(r.id)) AS places_left
         FROM events e
         LEFT JOIN registrations r ON r.event_id = e.id
         WHERE e.starts_at >= NOW()
           AND (e.title LIKE ? OR e.club LIKE ? OR e.description LIKE ? OR e.location LIKE ?)
         GROUP BY e.id
         ORDER BY e.starts_at ASC'
    );
    $stmt->execute([$like, $like, $like, $like]);
    return $stmt->fetchAll();
}
