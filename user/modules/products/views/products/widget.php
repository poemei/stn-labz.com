<?php

declare(strict_types=1);

/**
 * STN-Labz Widget Library
 *
 * Public-safe widgets for STN-Labz web properties.
 *
 * - No identifiers disclosed (no IPs, no UAs, no raw lists).
 * - Pulls data from STN API endpoints.
 * - Small, predictable, KISS.
 *
 * @package STN_Labz
 */

final class widget
{
    /**
     * Fetch JSON from a remote endpoint with a short file cache.
     *
     * @param string $url
     * @param int    $timeoutSeconds
     * @param int    $cacheTtlSeconds
     *
     * @return array<string,mixed>
     */
    public static function fetch_json(string $url, int $timeoutSeconds = 3, int $cacheTtlSeconds = 30): array
    {
        $cachePath = self::cache_path($url);

        if ($cacheTtlSeconds > 0 && is_file($cachePath)) {
            $age = time() - (int) filemtime($cachePath);
            if ($age >= 0 && $age <= $cacheTtlSeconds) {
                $cached = file_get_contents($cachePath);
                if (is_string($cached) && $cached !== '') {
                    $decoded = json_decode($cached, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            }
        }

        $body = self::http_get($url, $timeoutSeconds);

        if ($body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                self::cache_write($cachePath, $body);
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Render the Sentinel Intel widget (public-safe).
     *
     * @param string $intelUrl
     * @param array{
     *   title?:string,
     *   subtitle?:string,
     *   max_types?:int,
     *   cache_ttl?:int,
     *   timeout?:int
     * } $opts
     *
     * @return string
     */
    public static function render_sentinel_intel(string $intelUrl, array $opts = []): string
    {
        $title    = (string) ($opts['title'] ?? 'Sentinel Intel');
        $subtitle = (string) ($opts['subtitle'] ?? 'What we’re catching');
        $maxTypes = (int) ($opts['max_types'] ?? 6);

        $cacheTtl = (int) ($opts['cache_ttl'] ?? 30);
        $timeout  = (int) ($opts['timeout'] ?? 3);

        $payload = self::fetch_json($intelUrl, $timeout, $cacheTtl);

        $generatedAt   = self::safe_str($payload['generated_at'] ?? '');
        $totalThreats  = (int) self::safe_int($payload['stats']['total_threats'] ?? 0);
        $byType        = self::safe_assoc_ints($payload['stats']['by_type'] ?? []);
        $blocklists    = (array) ($payload['blocklists'] ?? []);

        $counts = [
            'IP addresses' => self::list_count($blocklists['ips'] ?? []),
            'User agents'  => self::list_count($blocklists['user_agents'] ?? []),
            'ASNs'         => self::list_count($blocklists['asns'] ?? []),
            'Ranges'       => self::list_count($blocklists['ranges'] ?? []),
            'Countries'    => self::list_count($blocklists['countries'] ?? []),
        ];

        $mostActive = self::most_active_type($byType);
        $topTypes   = self::top_types($byType, $maxTypes);

        $metaLine = '';
        if ($generatedAt !== '') {
            $metaLine = '<div class="stn-widget-meta">Updated: ' . htmlspecialchars($generatedAt, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        $html = '';
        $html .= '<section class="stn-widget stn-widget-intel" aria-label="Sentinel Intel">';
        $html .= '  <header class="stn-widget-header">';
        $html .= '    <div class="stn-widget-title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '    <div class="stn-widget-subtitle">' . htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '    <div class="stn-widget-note">Public-safe telemetry summary (no identifiers disclosed).</div>';
        $html .=        $metaLine;
        $html .= '  </header>';

        $html .= '  <div class="stn-widget-grid">';
        $html .= self::card_number(
            'Total threats observed',
            (string) $totalThreats,
            'Cumulative count from the active feed.'
        );

        $html .= self::card_number(
            'Blocklists maintained',
            (string) array_sum(array_values($counts)),
            'Counts only. Identifiers stay private.'
        );

        if ($mostActive !== '') {
            $html .= self::card_text(
                'Most active class',
                htmlspecialchars($mostActive, ENT_QUOTES, 'UTF-8'),
                'Highest count in the current feed.'
            );
        } else {
            $html .= self::card_text(
                'Most active class',
                '—',
                'No activity yet'
            );
        }

        $html .= self::card_types($topTypes);
        $html .= self::card_blocklist_counts($counts);

        $html .= '  </div>';

        $html .= '  <footer class="stn-widget-footer">';
        $html .= '    <div class="stn-widget-disclaimer">';
        $html .= '      We publish high-level stats only. Raw identifiers remain private.';
        $html .= '    </div>';
        $html .= '  </footer>';
        $html .= '</section>';

        return $html;
    }

    /**
     * Return the CSS for the widget (block format).
     *
     * @return string
     */
    public static function css(): string
    {
        return (string) implode("\n", [
            '',
            '/* STN Widget Library - Base Styles */',
            '.stn-widget {',
            '    border: 1px solid rgba(255, 255, 255, 0.12);',
            '    border-radius: 14px;',
            '    padding: 18px;',
            '    background: rgba(5, 12, 18, 0.85);',
            '    backdrop-filter: blur(6px);',
            '    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);',
            '}',
            '',
            '.stn-widget-header {',
            '    margin-bottom: 14px;',
            '}',
            '',
            '.stn-widget-title {',
            '    font-size: 18px;',
            '    font-weight: 700;',
            '    letter-spacing: 0.2px;',
            '}',
            '',
            '.stn-widget-subtitle {',
            '    margin-top: 2px;',
            '    font-size: 14px;',
            '    opacity: 0.9;',
            '}',
            '',
            '.stn-widget-note {',
            '    margin-top: 6px;',
            '    font-size: 12px;',
            '    opacity: 0.75;',
            '}',
            '',
            '.stn-widget-meta {',
            '    margin-top: 8px;',
            '    font-size: 12px;',
            '    opacity: 0.75;',
            '}',
            '',
            '.stn-widget-grid {',
            '    display: grid;',
            '    grid-template-columns: repeat(12, 1fr);',
            '    gap: 12px;',
            '}',
            '',
            '.stn-card {',
            '    border: 1px solid rgba(255, 255, 255, 0.10);',
            '    border-radius: 12px;',
            '    padding: 14px;',
            '    background: rgba(0, 0, 0, 0.28);',
            '}',
            '',
            '.stn-card h3 {',
            '    margin: 0;',
            '    font-size: 12px;',
            '    font-weight: 700;',
            '    letter-spacing: 0.4px;',
            '    text-transform: uppercase;',
            '    opacity: 0.85;',
            '}',
            '',
            '.stn-card .stn-value {',
            '    margin-top: 10px;',
            '    font-size: 28px;',
            '    font-weight: 800;',
            '    line-height: 1;',
            '}',
            '',
            '.stn-card .stn-value.stn-text {',
            '    font-size: 18px;',
            '    font-weight: 700;',
            '    line-height: 1.2;',
            '}',
            '',
            '.stn-card .stn-help {',
            '    margin-top: 10px;',
            '    font-size: 12px;',
            '    opacity: 0.75;',
            '    line-height: 1.35;',
            '}',
            '',
            '.stn-card.stn-span-4 {',
            '    grid-column: span 4;',
            '}',
            '',
            '.stn-card.stn-span-6 {',
            '    grid-column: span 6;',
            '}',
            '',
            '.stn-card.stn-span-12 {',
            '    grid-column: span 12;',
            '}',
            '',
            '.stn-list {',
            '    margin-top: 10px;',
            '    padding-left: 18px;',
            '    opacity: 0.92;',
            '}',
            '',
            '.stn-list li {',
            '    margin: 6px 0;',
            '}',
            '',
            '.stn-kv {',
            '    display: grid;',
            '    grid-template-columns: 1fr auto;',
            '    gap: 8px 12px;',
            '    margin-top: 10px;',
            '    font-size: 13px;',
            '}',
            '',
            '.stn-kv .k {',
            '    opacity: 0.85;',
            '}',
            '',
            '.stn-kv .v {',
            '    font-weight: 800;',
            '}',
            '',
            '.stn-widget-footer {',
            '    margin-top: 14px;',
            '    padding-top: 12px;',
            '    border-top: 1px solid rgba(255, 255, 255, 0.10);',
            '}',
            '',
            '.stn-widget-disclaimer {',
            '    font-size: 12px;',
            '    opacity: 0.75;',
            '}',
            '',
            '@media (max-width: 860px) {',
            '    .stn-card.stn-span-4 {',
            '        grid-column: span 12;',
            '    }',
            '    .stn-card.stn-span-6 {',
            '        grid-column: span 12;',
            '    }',
            '}',
            '',
        ]);
    }

    /**
     * @param string $label
     * @param string $value
     * @param string $help
     *
     * @return string
     */
    private static function card_number(string $label, string $value, string $help): string
    {
        $html = '';
        $html .= '<div class="stn-card stn-span-4">';
        $html .= '  <h3>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '  <div class="stn-value">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '  <div class="stn-help">' . htmlspecialchars($help, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param string $label
     * @param string $valueHtmlSafe
     * @param string $help
     *
     * @return string
     */
    private static function card_text(string $label, string $valueHtmlSafe, string $help): string
    {
        $html = '';
        $html .= '<div class="stn-card stn-span-4">';
        $html .= '  <h3>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h3>';
        $html .= '  <div class="stn-value stn-text">' . $valueHtmlSafe . '</div>';
        $html .= '  <div class="stn-help">' . htmlspecialchars($help, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<int,array{type:string,count:int}> $topTypes
     *
     * @return string
     */
    private static function card_types(array $topTypes): string
    {
        $html = '';
        $html .= '<div class="stn-card stn-span-6">';
        $html .= '  <h3>Activity by type</h3>';
        $html .= '  <div class="stn-help">Top ' . (int) count($topTypes) . '</div>';

        if (empty($topTypes)) {
            $html .= '  <div class="stn-value stn-text">No type breakdown available.</div>';
            $html .= '</div>';
            return $html;
        }

        $html .= '  <ol class="stn-list">';
        foreach ($topTypes as $row) {
            $html .= '    <li>' . htmlspecialchars($row['type'], ENT_QUOTES, 'UTF-8') . ' <strong>(' . (int) $row['count'] . ')</strong></li>';
        }
        $html .= '  </ol>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @param array<string,int> $counts
     *
     * @return string
     */
    private static function card_blocklist_counts(array $counts): string
    {
        $html = '';
        $html .= '<div class="stn-card stn-span-6">';
        $html .= '  <h3>Blocklists (counts only)</h3>';
        $html .= '  <div class="stn-help">Public-safe</div>';
        $html .= '  <div class="stn-kv">';

        foreach ($counts as $k => $v) {
            $html .= '    <div class="k">' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '    <div class="v">' . (int) $v . '</div>';
        }

        $html .= '  </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Simple HTTP GET with cURL if available, otherwise file_get_contents.
     *
     * @param string $url
     * @param int    $timeoutSeconds
     *
     * @return string
     */
    private static function http_get(string $url, int $timeoutSeconds): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return '';
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeoutSeconds);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSeconds);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

            $body = curl_exec($ch);

            curl_close($ch);

            return is_string($body) ? $body : '';
        }

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => $timeoutSeconds,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);

        return is_string($body) ? $body : '';
    }

    /**
     * @param mixed $v
     *
     * @return int
     */
    private static function safe_int($v): int
    {
        if (is_int($v)) {
            return $v;
        }

        if (is_numeric($v)) {
            return (int) $v;
        }

        return 0;
    }

    /**
     * @param mixed $v
     *
     * @return string
     */
    private static function safe_str($v): string
    {
        return is_string($v) ? trim($v) : '';
    }

    /**
     * Coerce an associative array of ints.
     *
     * @param mixed $v
     *
     * @return array<string,int>
     */
    private static function safe_assoc_ints($v): array
    {
        if (!is_array($v)) {
            return [];
        }

        $out = [];
        foreach ($v as $k => $val) {
            if (!is_string($k)) {
                continue;
            }
            $out[$k] = self::safe_int($val);
        }

        return $out;
    }

    /**
     * Determine the most active type from a by_type array.
     *
     * @param array<string,int> $byType
     *
     * @return string
     */
    private static function most_active_type(array $byType): string
    {
        if (empty($byType)) {
            return '';
        }

        arsort($byType);

        $keys = array_keys($byType);
        $top  = $keys[0] ?? '';

        return is_string($top) ? $top : '';
    }

    /**
     * Get top N types by count.
     *
     * @param array<string,int> $byType
     * @param int              $limit
     *
     * @return array<int,array{type:string,count:int}>
     */
    private static function top_types(array $byType, int $limit): array
    {
        if (empty($byType) || $limit <= 0) {
            return [];
        }

        arsort($byType);

        $out = [];
        $i   = 0;

        foreach ($byType as $type => $count) {
            $out[] = [
                'type'  => (string) $type,
                'count' => (int) $count,
            ];

            $i++;
            if ($i >= $limit) {
                break;
            }
        }

        return $out;
    }

    /**
     * Count a list safely (supports non-arrays).
     *
     * @param mixed $v
     *
     * @return int
     */
    private static function list_count($v): int
    {
        return is_array($v) ? count($v) : 0;
    }

    /**
     * Create a cache path based on URL hash.
     *
     * @param string $url
     *
     * @return string
     */
    private static function cache_path(string $url): string
    {
        $dir = self::cache_dir();

        return $dir . '/stn_widget_' . hash('sha256', $url) . '.json';
    }

    /**
     * Cache directory (inside /app/lib/_cache).
     *
     * @return string
     */
    private static function cache_dir(): string
    {
        $base = defined('STN_LiB') ? (string) STN_LiB : __DIR__;

        $dir = rtrim($base, '/') . '/_cache';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * @param string $path
     * @param string $body
     *
     * @return void
     */
    private static function cache_write(string $path, string $body): void
    {
        @file_put_contents($path, $body, LOCK_EX);
    }
}

