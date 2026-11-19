<?php
declare(strict_types=1);

namespace App\Repositories;

final class CalculatorRepository
{
    public function all(): array
    {
        return db()->query('SELECT * FROM calculators ORDER BY slug ASC')->fetchAll();
    }

    public function find(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM calculators WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * @return array<string, array<string, float|int>>
     */
    public function settings(): array
    {
        $settings = [];

        foreach ($this->all() as $row) {
            $decoded = json_decode((string) $row['coefficients'], true);
            if (is_array($decoded)) {
                $settings[$row['slug']] = $decoded;
            }
        }

        return $settings;
    }

    public function updateCoefficients(string $slug, array $coefficients): void
    {
        $stmt = db()->prepare('UPDATE calculators SET coefficients = ?, updated_at = CURRENT_TIMESTAMP WHERE slug = ?');
        $stmt->execute([json_encode($coefficients, JSON_UNESCAPED_UNICODE), $slug]);
    }
}
