<?php

// single entry point for PHP files accessed by browser
// all reqests go through here


declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require ROOT_PATH . '/app/Controllers/ApiController.php';

use App\Controllers\ApiController;

// Set JSON response header
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);

$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if (!$endpoint) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing endpoint']);
    exit;
}


$controller = new ApiController();

// Simple router - make one to switch via v1 (REGEX)
// Controller Echoes json_encodes
switch (true) {

    /* -------------------- Session -------------------- */
    case $endpoint === 'session_create' && $method === 'POST':
        $controller->createLLMSession();
        break;

    case $endpoint === 'session_get' && $method === 'POST':
        // getLLMSession
        break;


    case $endpoint === 'session_update' && $method === 'POST':
        // endLLMSession
        break;

    case $endpoint === 'session_delete' && $method === 'POST':
        // Delete LLM Session
        break;

    /* -------------------- Interactions -------------------- */
    case $endpoint === 'query' && $method === 'POST':
        $controller->queryInteractions();
        break;


    /* -------------------- LLM -------------------- */


    case $endpoint === 'v2/api/llm/report/create' && $method === 'POST':
        $controller->generateLLMReport();
        break;
    
    case $endpoint === 'v2/api/llm/report' && $method === 'GET':
        $controller->getMostRecentReport();
        break;

    case $endpoint === 'v2/api/llm/chat' && $method === 'POST':
        $controller->userLLMChat();
        break;

    case $endpoint === 'v2/api/llm/feedback' && $method === 'POST':
        $controller->submitLLMFeedback();
        break;




    /* -------------------- Default 404 -------------------- */
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
        break;
}
exit;
