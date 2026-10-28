<?php
/**
 * Market Controller
 *
 * Handles marketplace public views, admin management,
 * payment processing, downloads, and configuration.
 *
 * Path: /app/controllers/market.php
 *
 * @package Chaos MVC
 * @author [HUMAN: Mei]
 * @patch [AI:GPT-5.3 | 2026-04-09 UTC]
 */

class market extends controller
{
    public function index($params = null)
    {
        $target = is_array($params) ? ($params[0] ?? null) : $params;

        $model = $this->model('market_model');

        $data = [
            'categories'  => $model->get_categories() ?? [],
            'products'    => $target ? $model->get_by_cat($target) : $model->get_products(),
            'current_cat' => $target
        ];

        $this->view('market/index', $data);
    }

    public function admin()
    {
        $model = $this->model('market_model');

        $config = $model->get_all_config() ?? [];

        $data = [
            'categories' => $model->get_categories() ?? [],
            'products'   => $model->get_products() ?? [],
            'stats'      => $model->get_revenue() ?? [],
            'config'     => $config
        ];

        $this->view('admin/market', $data);
    }

    public function save_config()
    {
        if (!empty($_POST)) {
            $model = $this->model('market_model');

            $model->set_config('stripe_public', $_POST['stripe_public'] ?? '');
            $model->set_config('stripe_secret', $_POST['stripe_secret'] ?? '');
            $model->set_config('webhook_secret', $_POST['webhook_secret'] ?? '');
            $model->set_config('currency', $_POST['currency'] ?? 'usd');
        }

        header('Location: /market/admin');
        exit;
    }

    public function add_category()
    {
        if (!empty($_POST['name'])) {
            $this->model('market_model')->add_category(trim($_POST['name']));
        }

        header('Location: /market/admin');
        exit;
    }

    public function delete_category($params = [])
    {
        $id = is_array($params) ? ($params[0] ?? null) : $params;

        if ($id) {
            $this->model('market_model')->delete_category((int)$id);
        }

        header('Location: /market/admin');
        exit;
    }

    public function add_product()
    {
        if (
            empty($_POST['title']) ||
            empty($_POST['file_name']) ||
            empty($_POST['category']) ||
            $_POST['price'] === ''
        ) {
            die('Missing required fields');
        }

        $image = $_POST['image'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $target = APPROOT . '/../public/assets/market/' . basename($_FILES['image']['name']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = '/assets/market/' . basename($_FILES['image']['name']);
            }
        }

        $this->model('market_model')->add_product([
            'title'       => trim($_POST['title']),
            'file_name'   => trim($_POST['file_name']),
            'category'    => (int)$_POST['category'],
            'price'       => (float)$_POST['price'],
            'image'       => trim($image),
            'description' => $_POST['description'] ?? '',
            'certified'   => !empty($_POST['certified']) ? 1 : 0
        ]);

        header('Location: /market/admin');
        exit;
    }

    public function edit_product()
    {
        if (empty($_POST['id'])) {
            header('Location: /market/admin');
            exit;
        }

        $image = $_POST['image'] ?? '';

        if (!empty($_FILES['image']['name'])) {
            $target = APPROOT . '/../public/assets/market/' . basename($_FILES['image']['name']);

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = '/assets/market/' . basename($_FILES['image']['name']);
            }
        }

        $this->model('market_model')->update_product([
            'id'          => (int)$_POST['id'],
            'title'       => trim($_POST['title']),
            'file_name'   => trim($_POST['file_name']),
            'category'    => (int)$_POST['category'],
            'price'       => (float)$_POST['price'],
            'image'       => trim($image),
            'description' => $_POST['description'] ?? '',
            'certified'   => !empty($_POST['certified']) ? 1 : 0
        ]);

        header('Location: /market/admin');
        exit;
    }

    public function delete_product($params = [])
    {
        $id = is_array($params) ? ($params[0] ?? null) : $params;

        if ($id) {
            $this->model('market_model')->delete_product((int)$id);
        }

        header('Location: /market/admin');
        exit;
    }

    public function detail($params = null)
    {
        $title = is_array($params) ? ($params[0] ?? null) : $params;

        if (!$title) {
            header('Location: /market');
            exit;
        }

        $model = $this->model('market_model');
        $product = $model->get_product_by_title($title);

        if (!$product) {
            die('Product not found: ' . htmlspecialchars((string)$title));
        }

        // FIX: safe config
        $config = [
            'stripe_public' => $model->get_config('stripe_public') ?? ''
        ];

        $data = [
            'title'       => $product['title'] ?? '',
            'product'     => $product,
            'description' => $product['description'] ?? '',
            'image'       => $product['image'] ?? '',
            'certified'   => $product['certified'] ?? 0,
            'config'      => $config
        ];

        // 🔥 THIS WAS THE BREAK
        $this->view('market/detail', $data);
    }

    public function success($params = [])
    {
        $id = is_array($params) ? ($params[0] ?? null) : $params;

        if (!$id) {
            die('No product ID');
        }

        $this->view('market/success', ['product_id' => $id]);
    }

    public function download($params = [])
    {
        $id = is_array($params) ? ($params[0] ?? null) : $params;

        if (!$id) {
            die('Invalid product');
        }

        $model = $this->model('market_model');
        $product = $model->get_product_by_id($id);

        if (!$product || empty($product['file_name'])) {
            die('Invalid product file');
        }

        $file = APPROOT . '/../public/packages/' . trim($product['file_name']);

        if (!file_exists($file)) {
            die('File not found: ' . $product['file_name']);
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($file));

        readfile($file);
        exit;
    }

    public function intent($params = null)
    {
        header('Content-Type: application/json');

        $id = is_array($params) ? ($params[0] ?? null) : $params;

        if (!$id) {
            echo json_encode(['error' => 'Missing ID']);
            exit;
        }

        $model = $this->model('market_model');
        $product = $model->get_product_by_id($id);

        if (!$product) {
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        $config = $model->get_all_config() ?? [];

        \Stripe\Stripe::setApiKey($config['stripe_secret'] ?? '');

        try {
            $intent = \Stripe\PaymentIntent::create([
                'amount'   => (int)($product['price'] * 100),
                'currency' => $config['currency'] ?? 'usd',
                'metadata' => ['product_id' => $product['id']]
            ]);

            echo json_encode(['clientSecret' => $intent->client_secret]);

        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit;
    }

    public function webhook()
    {
        $payload = @file_get_contents('php://input');
        $event = json_decode($payload, true);

        if (!$event || empty($event['type'])) {
            http_response_code(400);
            exit;
        }

        if ($event['type'] === 'payment_intent.succeeded') {
            $intent = $event['data']['object'];

            $this->model('market_model')->log_sale([
                'product_id' => $intent['metadata']['product_id'] ?? null,
                'amount'     => ($intent['amount_received'] ?? 0) / 100
            ]);
        }

        http_response_code(200);
    }
}
