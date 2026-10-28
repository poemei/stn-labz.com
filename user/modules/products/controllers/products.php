<?php
declare(strict_types=1);

final class products extends controller
{
    public function index($url_params = null): void
    {
        $data = [
        'title' => 'Products',
        'sub_title' => 'We build for the Sovereign Human. No updates. No Cloud or AI dependency.'
        ];
        $this->view('products/index', $data);
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
	
	public function dct_win($url_params = null): void
    {
        $data = [
        'title' => 'Sentinel Threat Monitor',
        'sub_title' => 'Looking where others don’t..'
        ];
        $this->view('products/dct_win', $data);
    }
	
	public function module_builder($url_params = null): void
    {
        $data = [
        'title' => 'Module Builder',
        'sub_title' => 'Module Builder is a Chaos MVC development tool for creating properly structured modules without having to build the foundation by hand.'
        ];
        $this->view('products/module_builder', $data);
    }

public function digit($url_params = null): void
    {
        $data = [
        'title' => 'The Digit Agent Project',
        'sub_title' => 'Data without the LLM'
        ];
        $this->view('products/digit', $data);
    }
}
