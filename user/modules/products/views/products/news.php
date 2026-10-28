<?php
declare(strict_types=1);

final class News extends controller
{
    public function index($url_params = null): void
    {
        $data = [
        'title' => 'News',
        'sub_title' => 'Latest updates from the STN-Labz network, infrastructure improvements, and release notes from across the Sentinel ecosystem.'
        ];
        $this->view('news/index', $data);
    }
    
    public function space($url_params = null): void
    {
        $data = [
        'title' => 'Earth, Space, and Interstellar Implementations',
        'sub_title' => 'STN-Labz is advancing into space-capable environments with a focus on secure, reliable, and deterministic system behavior across Earth-based infrastructure, orbital platforms, and future interstellar operations.'
        ];
        $this->view('products/space', $data);
    }
    
    public function sentinel($url_params = null): void
    {
        $data = [
        'title' => 'Sentinel Threat Monitor',
        'sub_title' => 'Looking where others don’t..'
        ];
        $this->view('products/sentinel', $data);
    }
}
