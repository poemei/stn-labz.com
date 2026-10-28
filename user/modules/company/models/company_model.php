<?php
declare(strict_types=1);

/**
 * STN-LABZ
 * Company Module Model
 *
 * Storage:
 * - Careers and Acknowledgments: module-owned JSON
 * - security.txt: public/.well-known/security.txt
 */
final class company_model extends model
{
    private const FILE = __DIR__ . '/../data/company.json';

    private const SECURITY_FILE =
        APPROOT . '/../public/.well-known/security.txt';

    public const PAGES = [
        'careers',
        'acknowledgments',
		'strategic',
    ];

    /**
     * Company uses file-backed storage and does not require
     * a database connection.
     */
    public function __construct()
    {
        // Intentionally do not call the database-backed parent constructor.
    }

    /**
     * Read all JSON-backed company data.
     *
     * @return array<string, array<string, string>>
     */
    public function getData(): array
    {
        $file = @fopen(self::FILE, 'rb');

        if ($file === false) {
            throw new RuntimeException(
                'Company data could not be opened.'
            );
        }

        try {
            if (!flock($file, LOCK_SH)) {
                throw new RuntimeException(
                    'Company data could not be locked.'
                );
            }

            $raw = stream_get_contents($file);

            return $this->decode($raw);
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    /**
     * Save one JSON-backed company page.
     */
    public function savePage(
        string $slug,
        string $title,
        string $content
    ): void {
        if (!in_array($slug, self::PAGES, true)) {
            throw new InvalidArgumentException(
                'Unknown company page.'
            );
        }

        $title = trim($title);
        $content = trim($content);

        if ($title === '') {
            throw new InvalidArgumentException(
                'Title is required.'
            );
        }

        $this->saveSection(
            $slug,
            [
                'title' => $title,
                'content' => $content,
            ]
        );
    }

    /**
     * Read the canonical public security.txt file.
     */
    public function getSecurityText(): string
    {
        if (!is_file(self::SECURITY_FILE)) {
            throw new RuntimeException(
                'security.txt does not exist.'
            );
        }

        if (!is_readable(self::SECURITY_FILE)) {
            throw new RuntimeException(
                'security.txt is not readable.'
            );
        }

        $contents = file_get_contents(self::SECURITY_FILE);

        if ($contents === false) {
            throw new RuntimeException(
                'security.txt could not be read.'
            );
        }

        return $contents;
    }

    /**
     * Update the canonical public security.txt file.
     */
    public function saveSecurityText(string $contents): void
    {
        $contents = str_replace(
            ["\r\n", "\r"],
            "\n",
            $contents
        );

        if (trim($contents) === '') {
            throw new InvalidArgumentException(
                'security.txt cannot be empty.'
            );
        }

        $directory = dirname(self::SECURITY_FILE);

        if (!is_dir($directory)) {
            throw new RuntimeException(
                'security.txt directory does not exist.'
            );
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(
                'security.txt directory is not writable.'
            );
        }

        $file = @fopen(self::SECURITY_FILE, 'c+b');

        if ($file === false) {
            throw new RuntimeException(
                'security.txt could not be opened for writing.'
            );
        }

        try {
            if (!flock($file, LOCK_EX)) {
                throw new RuntimeException(
                    'security.txt could not be locked.'
                );
            }

            rewind($file);

            $length = strlen($contents);
            $offset = 0;

            while ($offset < $length) {
                $written = fwrite(
                    $file,
                    substr($contents, $offset)
                );

                if ($written === false || $written === 0) {
                    throw new RuntimeException(
                        'security.txt could not be written.'
                    );
                }

                $offset += $written;
            }

            if (!ftruncate($file, $length)) {
                throw new RuntimeException(
                    'security.txt could not be truncated.'
                );
            }

            if (!fflush($file)) {
                throw new RuntimeException(
                    'security.txt could not be completed.'
                );
            }
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }

    /**
     * Decode and validate company JSON data.
     *
     * @return array<string, array<string, string>>
     */
    private function decode(mixed $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            throw new RuntimeException(
                'Company data is empty or unreadable.'
            );
        }

        try {
            $data = json_decode(
                $raw,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException(
                'Company data contains invalid JSON.',
                0,
                $e
            );
        }

        if (!is_array($data)) {
            throw new RuntimeException(
                'Company data must be an object.'
            );
        }

        foreach (self::PAGES as $slug) {
            if (
                !isset($data[$slug])
                || !is_array($data[$slug])
                || !is_string($data[$slug]['title'] ?? null)
                || !is_string($data[$slug]['content'] ?? null)
            ) {
                throw new RuntimeException(
                    'Company page data is invalid: ' . $slug
                );
            }
        }

        return $data;
    }

    /**
     * Save one JSON-backed company section.
     *
     * @param array<string, string> $section
     */
    private function saveSection(
        string $key,
        array $section
    ): void {
        $file = @fopen(self::FILE, 'r+b');

        if ($file === false) {
            throw new RuntimeException(
                'Company data is not writable.'
            );
        }

        try {
            if (!flock($file, LOCK_EX)) {
                throw new RuntimeException(
                    'Company data could not be locked.'
                );
            }

            $raw = stream_get_contents($file);
            $data = $this->decode($raw);

            $data[$key] = $section;

            $json = json_encode(
                $data,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_THROW_ON_ERROR
            ) . "\n";

            rewind($file);

            $length = strlen($json);
            $offset = 0;

            while ($offset < $length) {
                $written = fwrite(
                    $file,
                    substr($json, $offset)
                );

                if ($written === false || $written === 0) {
                    throw new RuntimeException(
                        'Company data could not be written.'
                    );
                }

                $offset += $written;
            }

            if (!ftruncate($file, $length)) {
                throw new RuntimeException(
                    'Company data could not be truncated.'
                );
            }

            if (!fflush($file)) {
                throw new RuntimeException(
                    'Company data could not be completed.'
                );
            }
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
        }
    }
}