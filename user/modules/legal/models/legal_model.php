<?php
declare(strict_types=1);

// path: /user/modules/legal/models/legal_model.php

/*
 * [AI:GPT-5.6 Sol | 2026-09-01 21:49:00 UTC]
 */

/**
 * Legal module model.
 */
class legal_model extends model
{
    /**
     * Module-owned JSON data file.
     */
    private const DATA_FILE = __DIR__ . '/../data/legal.json';

    /**
     * Documents that may be administered by this module.
     */
    private const DOCUMENTS = [
        'privacy',
        'terms',
    ];

    /**
     * Return basic module information.
     *
     * @return array
     */
    public function getModuleInformation(): array
    {
        return [
            'name' => 'Legal',
            'slug' => 'legal',
        ];
    }

    /**
     * Return all legal documents.
     *
     * @return array
     */
    public function getDocuments(): array
    {
        $data = $this->readData();

        $documents = [];

        foreach (self::DOCUMENTS as $document) {
            $documents[$document] = $this->normalizeDocument(
                $data[$document] ?? []
            );
        }

        return $documents;
    }

    /**
     * Return one legal document.
     *
     * @param string $document Document identifier.
     *
     * @return array
     */
    public function getDocument(string $document): array
    {
        $this->validateDocumentName($document);

        $data = $this->readData();

        return $this->normalizeDocument(
            $data[$document] ?? []
        );
    }

    /**
     * Save one legal document.
     *
     * @param string $document Document identifier.
     * @param string $title Document title.
     * @param string $content Document HTML content.
     *
     * @return void
     */
    public function saveDocument(
        string $document,
        string $title,
        string $content
    ): void {
        $this->validateDocumentName($document);

        $title = trim($title);
        $content = trim($content);

        if ($title === '') {
            throw new InvalidArgumentException(
                'Legal document title is required.'
            );
        }

        if ($content === '') {
            throw new InvalidArgumentException(
                'Legal document content is required.'
            );
        }

        $data = $this->readData();

        $data[$document] = [
            'title' => $title,
            'content' => $content,
        ];

        $this->writeData($data);
    }

    /**
     * Read the module-owned legal JSON file.
     *
     * @return array
     */
    private function readData(): array
    {
        if (!is_file(self::DATA_FILE)) {
            return [];
        }

        $contents = file_get_contents(self::DATA_FILE);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $data = json_decode($contents, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                'Legal module data file contains invalid JSON.'
            );
        }

        return $data;
    }

    /**
     * Write the complete module-owned legal JSON file.
     *
     * @param array $data Legal document data.
     *
     * @return void
     */
    private function writeData(array $data): void
    {
        $directory = dirname(self::DATA_FILE);

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new RuntimeException(
                'Unable to create Legal module data directory.'
            );
        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            throw new RuntimeException(
                'Unable to encode Legal module data.'
            );
        }

        $result = file_put_contents(
            self::DATA_FILE,
            $json . PHP_EOL,
            LOCK_EX
        );

        if ($result === false) {
            throw new RuntimeException(
                'Unable to write Legal module data.'
            );
        }
    }

    /**
     * Validate a legal document identifier.
     *
     * @param string $document Document identifier.
     *
     * @return void
     */
    private function validateDocumentName(string $document): void
    {
        if (!in_array($document, self::DOCUMENTS, true)) {
            throw new InvalidArgumentException(
                'Invalid legal document.'
            );
        }
    }

    /**
     * Normalize document data for views.
     *
     * @param mixed $document Stored document data.
     *
     * @return array
     */
    private function normalizeDocument($document): array
    {
        if (!is_array($document)) {
            $document = [];
        }

        return [
            'title' => (string) ($document['title'] ?? ''),
            'content' => (string) ($document['content'] ?? ''),
        ];
    }
}

/* [End AI:GPT-5.6 Sol] */