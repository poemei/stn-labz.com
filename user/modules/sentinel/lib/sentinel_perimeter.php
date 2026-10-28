<?php
// path: /user/modules/sentinel/lib/sentinel_perimeter.php

final class sentinel_perimeter
{
    /**
     * Inspect one request at the approved pre-dispatch integration point.
     * Enforcement uses local data; API sync is an explicit Admin action.
     */
    public static function inspect(): void
    {
        $modelFile = dirname(__DIR__) . '/models/sentinel_model.php';

        if (!is_file($modelFile) || is_link($modelFile)) {
            return;
        }

        require_once $modelFile;

        try {
            $model = new sentinel_model();

            if (!$model->schema_available()) {
                return;
            }

            $config = $model->get_config_map();
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

            // Local whitelist takes precedence over blocks and URI patterns.
            if ($model->is_whitelisted($ip)) {
                return;
            }

            if ($model->is_blocked($ip)) {
                self::terminate_request('IP Blocked by Sentinel Global Intel');
            }

            $patterns = explode(
                ',',
                (string) ($config['malicious_patterns'] ?? '')
            );

            foreach ($patterns as $pattern) {
                $pattern = trim($pattern);

                if ($pattern !== '' && stripos($uri, $pattern) !== false) {
                    $model->block_ip($ip);
                    // This branch deterministically terminates with HTTP 403.
                    $model->log_threat($ip, 'pattern_match', $uri, 403);
                    self::terminate_request(
                        'Threat Pattern Detected: ' . $pattern
                    );
                }
            }
        } catch (Throwable $error) {
            error_log(
                'Sentinel inspection unavailable: '
                . $error->getMessage()
            );
        }
    }

    private static function terminate_request(string $reason): never
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');

        die(
            '<h3>Sentinel Security</h3><p>Access denied: '
            . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8')
            . '</p>'
        );
    }
}
