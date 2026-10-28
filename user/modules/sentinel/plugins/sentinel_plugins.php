<?php
declare(strict_types=1);

// Sentinel plugin boundary. Registered enhancements only; never invoked in perimeter.
final class sentinel_plugins
{
    private const REGISTRY = [
        'httpbl' => ['file' => 'httpbl/httpbl.php', 'class' => 'sentinel_httpbl'],
    ];

    public static function available(): array
    {
        return array_keys(self::REGISTRY);
    }

    public static function lookup(string $plugin, string $ip, array $config): array
    {
        if (!isset(self::REGISTRY[$plugin])) {
            throw new InvalidArgumentException('Unknown Sentinel plugin.');
        }
        $entry = self::REGISTRY[$plugin];
        $root = realpath(__DIR__);
        $file = __DIR__ . '/' . $entry['file'];
        if (!is_file($file) || is_link($file) || !$root || strpos((string) realpath($file), $root . DIRECTORY_SEPARATOR) !== 0) {
            throw new RuntimeException('Sentinel plugin unavailable.');
        }
        require_once $file;
        $handler = $entry['class'];
        if (!class_exists($handler, false) || !method_exists($handler, 'lookup')) {
            throw new RuntimeException('Sentinel plugin capability unavailable.');
        }
        return $handler::lookup($ip, $config);
    }
}
