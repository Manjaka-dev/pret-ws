<?php

namespace app\utils;

class CorsUtil {
    
    private array $config;
    
    public function __construct(array $config) {
        $this->config = $config;
    }
    
    public function handle(): void {
        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            $this->setCorsHeaders();
            http_response_code(200);
            exit();
        }
        
        // Set CORS headers for all requests
        $this->setCorsHeaders();
    }
    
    private function setCorsHeaders(): void {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Check if origin is allowed
        if (in_array($origin, $this->config['allowed_origins']) || in_array('*', $this->config['allowed_origins'])) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: ' . implode(', ', $this->config['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $this->config['allowed_headers']));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24 hours
    }
}