<?php
declare(strict_types=1);

class home extends controller
{
    public function index($url_params = null): void
    {
        $announcements = $this->module_model(
            'announcements',
            'announcements_model'
        );

        $data['featured_announcement'] =
            $announcements->get_latest_single();

        $this->view('index', $data);
    }
}
