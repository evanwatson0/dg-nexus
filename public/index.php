<?php

// single entry point for PHP files accessed by browser
// all reqests go through here


declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require ROOT_PATH . '/authentication/auth_check.php';

use App\Controllers\ApiController;

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new ApiController();

// Simple router
switch (true) {

    /* -------------------- Interactions -------------------- */

    /* -------------------- LLM -------------------- */
    case $uri === '/api/llm/session/create' && $method === 'POST':
        $controller->createLLMSession();
        break;


    case $uri === '/api/llm/report' && $method === 'POST':
        

        $controller->generateLLMReport();
        break;

    case $uri === '/api/llm/chat' && $method === 'POST':
        $controller->userLLMChat();
        break;

    case $uri === '/api/llm/feedback' && $method === 'POST':
        $controller->submitLLMFeedback();
        break;

    /* -------------------- Default 404 -------------------- */
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}