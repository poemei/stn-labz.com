<?php
declare(strict_types=1);

/**
 * STN-LABZ
 * Security Research Target Model
 *
 * app/models/srt_model.php
 */

final class srt_model extends model
{
    /**
     * Return all SRT records newest first.
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                srt_id,
                title,
                status,
                classification,
                summary,
                source_intelligence_id,
                authorized_by,
                authorized_at,
                created_at,
                updated_at
            FROM srts
            ORDER BY authorized_at DESC, id DESC
        ";

        return $this->fetchAll($sql);
    }

    /**
     * Return one SRT by permanent SRT identifier.
     */
    public function getBySrtId(string $srtId): array|false
    {
        $sql = "
            SELECT
                srt_id,
                title,
                status,
                classification,
                summary,
                source_intelligence_id,
                authorized_by,
                authorized_at,
                created_at,
                updated_at
            FROM srts
            WHERE srt_id = :srt_id
            LIMIT 1
        ";

        return $this->fetch($sql, [
            'srt_id' => $srtId
        ]);
    }

    /**
     * Check whether an SRT identifier already exists.
     */
    public function existsBySrtId(string $srtId): bool
    {
        $sql = "
            SELECT srt_id
            FROM srts
            WHERE srt_id = :srt_id
            LIMIT 1
        ";

        return $this->fetch($sql, [
            'srt_id' => $srtId
        ]) !== false;
    }

    /**
     * Create a permanent SRT record.
     */
    public function create(array $data): bool
    {
        $sql = "
            INSERT INTO srts (
                srt_id,
                title,
                status,
                classification,
                summary,
                source_intelligence_id,
                authorized_by,
                authorized_at
            )
            VALUES (
                :srt_id,
                :title,
                :status,
                :classification,
                :summary,
                :source_intelligence_id,
                :authorized_by,
                :authorized_at
            )
        ";

        return $this->query($sql, [
            'srt_id' => $data['srt_id'],
            'title' => $data['title'],
            'status' => $data['status'],
            'classification' => $data['classification'] ?: null,
            'summary' => $data['summary'],
            'source_intelligence_id' => $data['source_intelligence_id'] ?: null,
            'authorized_by' => $data['authorized_by'],
            'authorized_at' => $data['authorized_at']
        ]) !== false;
    }

    /**
     * Update an existing SRT.
     *
     * The permanent srt_id is used to identify the record
     * and is never modified.
     */
    public function updateSrt(string $srtId, array $data): bool
    {
        $sql = "
            UPDATE srts
            SET
                title = :title,
                status = :status,
                classification = :classification,
                summary = :summary,
                source_intelligence_id = :source_intelligence_id,
                authorized_by = :authorized_by,
                authorized_at = :authorized_at
            WHERE srt_id = :srt_id
        ";

        return $this->query($sql, [
            'title' => $data['title'],
            'status' => $data['status'],
            'classification' => $data['classification'] ?: null,
            'summary' => $data['summary'],
            'source_intelligence_id' => $data['source_intelligence_id'] ?: null,
            'authorized_by' => $data['authorized_by'],
            'authorized_at' => $data['authorized_at'],
            'srt_id' => $srtId
        ]) !== false;
    }
}