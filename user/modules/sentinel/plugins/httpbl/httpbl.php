<?php
declare(strict_types=1);

// Optional, admin-initiated reputation enhancement. No effect on enforcement.
final class sentinel_httpbl
{
    public static function lookup(string $ip, array $config): array
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            throw new InvalidArgumentException('http:BL accepts IPv4 addresses only.');
        }
        $key = (string) ($config['httpbl_key'] ?? '');
        if (!preg_match('/\\A[a-z]{12}\\z/', $key)) {
            throw new RuntimeException('http:BL is not configured. Sentinel remains operational.');
        }
        if (!function_exists('dns_get_record')) {
            throw new RuntimeException('DNS resolver unavailable; Sentinel remains operational.');
        }
        $query = $key . '.' . implode('.', array_reverse(explode('.', $ip))) . '.dnsbl.httpbl.org';
        $answers = @dns_get_record($query, DNS_A);
        unset($query, $key);
        $result = ['ip' => $ip, 'time_utc' => gmdate('Y-m-d H:i:s'),
            'status' => 'INCOMPLETE', 'answer' => null, 'days' => null,
            'score' => null, 'type_bits' => null, 'provider' => 'http:BL'];
        if ($answers === false) {
            return $result;
        }
        if ($answers === []) {
            $result['status'] = 'NO_A_RECORD_OBSERVED';
            return $result;
        }
        foreach ($answers as $answer) {
            $address = $answer['ip'] ?? '';
            if (!is_string($address) || !preg_match('/\\A127\\.(\\d{1,3})\\.(\\d{1,3})\\.(\\d{1,3})\\z/', $address, $match)) {
                continue;
            }
            if ((int) $match[1] > 255 || (int) $match[2] > 255 || (int) $match[3] > 7) {
                continue;
            }
            $result['status'] = 'LISTED';
            $result['answer'] = $address;
            $result['days'] = (int) $match[1];
            $result['score'] = (int) $match[2];
            $result['type_bits'] = (int) $match[3];
            return $result;
        }
        $result['status'] = 'UNRECOGNIZED_RESPONSE';
        return $result;
    }
}
