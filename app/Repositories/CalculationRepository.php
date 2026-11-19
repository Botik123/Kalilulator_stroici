<?php
declare(strict_types=1);

namespace App\Repositories;

final class CalculationRepository
{
    public function create(string $calculatorSlug, array $inputs, array $results): int
    {
        $stmt = db()->prepare(
            'INSERT INTO calculations (calculator_slug, inputs, results, ip_hash, created_at)
             VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)'
        );

        $stmt->execute([
            $calculatorSlug,
            json_encode($inputs, JSON_UNESCAPED_UNICODE),
            json_encode($results, JSON_UNESCAPED_UNICODE),
            client_ip_hash(),
        ]);

        return (int) db()->lastInsertId();
    }

    public function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM calculations')->fetchColumn();
    }

    public function recent(int $limit = 10): array
    {
        $stmt = db()->query('SELECT * FROM calculations ORDER BY id DESC LIMIT ' . max(1, $limit));

        return $stmt->fetchAll();
    }

    public function countSince(string $date): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM calculations WHERE created_at >= ?');
        $stmt->execute([$date]);

        return (int) $stmt->fetchColumn();
    }
}
