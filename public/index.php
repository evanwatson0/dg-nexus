<?php

// single entry point for PHP files accessed by browser
// all reqests go through here


declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require ROOT_PATH . '/app/auth/auth_check.php';

use App\Controllers\ApiController;

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$controller = new ApiController();

// Simple router - make one to switch via v1 (REGEX)
// Controller Echoes json_encodes
switch (true) {

    /* -------------------- Session -------------------- */
    case $uri === 'v2/api/session/create' && $method === 'POST':
        $controller->createLLMSession();
        break;

    case $uri === 'v2/api/session' && $method === 'GET':

        break;

    case $uri === 'v2/api/llm/session/update' && $method === 'POST':

        break;

    case $uri === 'v2/api/llm/session/delete' && $method === 'POST':

        break;

    /* -------------------- LLM -------------------- */
    case $uri === 'v2/api/query' && $method === 'GET':
        $controller->queryInteractions();
        break;


    /* -------------------- LLM -------------------- */


    case $uri === 'v2/api/llm/report/create' && $method === 'POST':
        $controller->generateLLMReport();
        break;
    
    case $uri === 'v2/api/llm/report' && $method === 'GET':
        $controller->getMostRecentReport();
        break;

    case $uri === 'v2/api/llm/chat' && $method === 'POST':
        $controller->userLLMChat();
        break;

    case $uri === 'v2/api/llm/feedback' && $method === 'POST':
        $controller->submitLLMFeedback();
        break;


    /* -------------------- LLM -------------------- */


    /* -------------------- Default 404 -------------------- */
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}
