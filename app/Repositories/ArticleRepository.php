<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ArticleRepository
{
    public function published(int $limit = 0): array
    {
        $sql = "SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC, id DESC";
        if ($limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        return db()->query($sql)->fetchAll();
    }

    public function all(): array
    {
        return db()->query('SELECT * FROM articles ORDER BY updated_at DESC, id DESC')->fetchAll();
    }

    public function findBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT * FROM articles WHERE slug = ?';
        if ($publishedOnly) {
            $sql .= " AND status = 'published'";
        }
        $sql .= ' LIMIT 1';

        $stmt = db()->prepare($sql);
        $stmt->execute([$slug]);
        $article = $stmt->fetch();

        return $article ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM articles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $article = $stmt->fetch();

        return $article ?: null;
    }

    public function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO articles
             (slug, title, excerpt, content, cover_image, meta_title, meta_description, calculator_slug, status, views, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );

        $stmt->execute([
            $data['slug'],
            $data['title'],
            $data['excerpt'],
            $data['content'],
            $data['cover_image'] ?: null,
            $data['meta_title'] ?: null,
            $data['meta_description'] ?: null,
            $data['calculator_slug'] ?: null,
            $data['status'],
        ]);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE articles SET
                slug = ?, title = ?, excerpt = ?, content = ?, cover_image = ?,
                meta_title = ?, meta_description = ?, calculator_slug = ?, status = ?,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = ?'
        );

        $stmt->execute([
            $data['slug'],
            $data['title'],
            $data['excerpt'],
            $data['content'],
            $data['cover_image'] ?: null,
            $data['meta_title'] ?: null,
            $data['meta_description'] ?: null,
            $data['calculator_slug'] ?: null,
            $data['status'],
            $id,
        ]);
    }

    public function delete(int $id): void
    {
        db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
    }

    public function incrementViews(int $id): void
    {
        db()->prepare('UPDATE articles SET views = views + 1 WHERE id = ?')->execute([$id]);
    }

    public function count(?string $status = null): int
    {
        if ($status === null) {
            return (int) db()->query('SELECT COUNT(*) FROM articles')->fetchColumn();
        }

        $stmt = db()->prepare('SELECT COUNT(*) FROM articles WHERE status = ?');
        $stmt->execute([$status]);

        return (int) $stmt->fetchColumn();
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM articles WHERE slug = ?';
        $params = [$slug];

        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $base = $slug;
        $suffix = 2;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function related(int $excludeId, int $limit = 3): array
    {
        $stmt = db()->prepare(
            "SELECT * FROM articles
             WHERE status = 'published' AND id <> ?
             ORDER BY created_at DESC, id DESC
             LIMIT " . max(1, $limit)
        );
        $stmt->execute([$excludeId]);

        return $stmt->fetchAll();
    }

    public function forCalculator(string $calculatorSlug, int $excludeId = 0): array
    {
        $stmt = db()->prepare(
            "SELECT * FROM articles
             WHERE status = 'published' AND calculator_slug = ? AND id <> ?
             ORDER BY created_at DESC LIMIT 3"
        );
        $stmt->execute([$calculatorSlug, $excludeId]);

        return $stmt->fetchAll();
    }
}
