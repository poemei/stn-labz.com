<?php
/**
 * Market Model
 *
 * Handles all database interactions for:
 * - configuration
 * - products
 * - categories
 * - orders / revenue
 *
 * @package ChaosMVC
 * @author [HUMAN:Mei | 2026-03-21 UTC]
 * @patch [AI:GPT-5.3 | 2026-03-25 UTC]
 */

class market_model extends model
{
    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    public function get_config($key)
    {
        $row = $this->fetch(
            "SELECT setting_value FROM market_config WHERE setting_key = ? LIMIT 1",
            [$key]
        );

        return $row['setting_value'] ?? null;
    }

    public function set_config($key, $value)
    {
        return $this->query(
            "INSERT INTO market_config (setting_key, setting_value)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)",
            [$key, $value]
        );
    }

    /**
     * Resolve full config (DB → fallback → defaults)
     */
    public function get_all_config()
    {
        require USERROOT . '/modules/market/config/market.php';

        return [
            'stripe_public' => $this->get_config('stripe_public') ?: (defined('STRIPE_PUBLIC') ? STRIPE_PUBLIC : ''),
            'stripe_secret' => $this->get_config('stripe_secret') ?: (defined('STRIPE_SECRET') ? STRIPE_SECRET : ''),
            'webhook_secret' => $this->get_config('webhook_secret') ?: (defined('WEBHOOK_SECRET') ? WEBHOOK_SECRET : ''),
            'currency'      => $this->get_config('currency') ?: 'usd'
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    public function get_products()
    {
        return $this->query(
            "SELECT p.*, c.name AS category_name
             FROM market_products p
             LEFT JOIN market_categories c ON p.category = c.id
             ORDER BY p.id DESC"
        )->fetchAll() ?? [];
    }

    public function get_product_by_id($id)
    {
        return $this->fetch(
            "SELECT * FROM market_products WHERE id = ? LIMIT 1",
            [(int)$id]
        );
    }

    public function get_product_by_title($title)
    {
        return $this->fetch(
            "SELECT * FROM market_products WHERE title = ? LIMIT 1",
            [$title]
        );
    }

    public function get_by_cat($name)
    {
        return $this->query(
            "SELECT p.*, c.name AS category_name
             FROM market_products p
             LEFT JOIN market_categories c ON p.category = c.id
             WHERE c.name = ?
             ORDER BY p.id DESC",
            [$name]
        )->fetchAll() ?? [];
    }

    public function add_product($data)
    {
        return $this->query(
            "INSERT INTO market_products 
            (title, file_name, category, price, image, description, certified)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $data['title'],
                $data['file_name'],
                (int)$data['category'],
                (float)$data['price'],
                $data['image'],
                $data['description'],
                (int)$data['certified']
            ]
        );
    }

    public function update_product($data)
    {
        return $this->query(
            "UPDATE market_products
             SET title = ?, file_name = ?, category = ?, price = ?, image = ?, description = ?, certified = ?
             WHERE id = ?",
            [
                $data['title'],
                $data['file_name'],
                (int)$data['category'],
                (float)$data['price'],
                $data['image'],
                $data['description'],
                (int)$data['certified'],
                (int)$data['id']
            ]
        );
    }

    public function delete_product($id)
    {
        return $this->query(
            "DELETE FROM market_products WHERE id = ?",
            [(int)$id]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORIES
    |--------------------------------------------------------------------------
    */

    public function get_categories()
    {
        return $this->query(
            "SELECT * FROM market_categories ORDER BY name ASC"
        )->fetchAll() ?? [];
    }

    public function add_category($name)
    {
        return $this->query(
            "INSERT INTO market_categories (name) VALUES (?)",
            [trim($name)]
        );
    }

    public function delete_category($id)
    {
        return $this->query(
            "DELETE FROM market_categories WHERE id = ?",
            [(int)$id]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATS
    |--------------------------------------------------------------------------
    */

    public function get_revenue()
    {
        $row = $this->fetch(
            "SELECT 
                COUNT(*) AS total_orders, 
                COALESCE(SUM(amount), 0) AS total_revenue 
             FROM market_orders"
        );

        return $row ?? ['total_orders' => 0, 'total_revenue' => 0];
    }

    public function log_sale($data)
    {
        return $this->query(
            "INSERT INTO market_orders (product_id, amount, created_at)
             VALUES (?, ?, NOW())",
            [
                (int)$data['product_id'],
                (float)$data['amount']
            ]
        );
    }
}
