<?php
namespace App\Controllers;



require_once ROOT_PATH . '/config/db_connect.php';
require ROOT_PATH . '/app/Services/GeneDrugDataPipeline.php';
require ROOT_PATH . '/app/Services/LLMDataPipeline.php';

use App\Services\GeneDrugDataPipeline;
use App\Services\LLMDataPipeline;

class ApiController
{
    protected $conn;
    protected $geneDrugPipeline;
    protected $llmPipeline;
    

    public function __construct()
    {
        $this->conn = get_connection();
        $this->geneDrugPipeline = new GeneDrugDataPipeline();
        $this->llmPipeline = new LLMDataPipeline();
    }

    /* -------------------- Interactions -------------------- */

    public function queryInteractions()
    {
        // $data = $_POST;
        $data = json_decode(file_get_contents('php://input'), true);
        $gene = $data['gene'] ?? null;
        $drug = $data['drug'] ?? null;
        $relation_type = $data['relation_type'] ?? '';

        if ($gene && $drug) {
                echo json_encode([
                'success' => false,
                'data' => null
            ]);
        }

        $results = $this->geneDrugPipeline->retrieveRelations($gene, $drug, $relation_type);


        echo json_encode([
            'success' => true,
            'data' => $results,
        ]);
    }

    // public function syncInteractions()
    // {
    //     $data = json_decode(file_get_contents('php://input'), true);
    //     $gene = $data['gene'] ?? '';
    //     $drug = $data['drug'] ?? '';

    //     $payload = dgi_db_req($gene, $drug);
    //     $this->geneDrugPipeline->insertRelations($payload);

    //     echo json_encode([
    //         'success' => true,
    //         'message' => 'Interactions synced successfully'
    //     ]);
    // }

    /* -------------------- LLM -------------------- */

    public function createLLMSession()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userIdentifier = $data['user_identifier'] ?? null;

        $this->llmPipeline->createLLMSession($userIdentifier);

        echo json_encode(['success' => true]);
    }

    public function generateLLMReport()
    {
        
        $data = json_decode(file_get_contents('php://input'), true);
        $query_data = $data['query'] ?? null;
        // $query_data = json_decode($query_json, true);

        $input = $data['input'] ?? Null;
        $type = $data['type'] ?? Null;
        $relation_type = $data['relation_type'] ?? Null;
        
        // TODO: Query_data currently comes back NULL right now
        if (!$input || !$type || !$query_data) {
            echo json_encode([
            'success' => false,
            'data' => $data,
            'response_id' => Null
        ]);
        }

        $response = $this->llmPipeline->generateReport($query_data, $input, $type, $relation_type);

        echo json_encode([
            'success' => true,
            'data' => $response['text'],
            'response_id' => $response['id']
        ]);
    }

    public function getMostRecentReport(): string {
        return $this->llmPipeline->getMostRecentReport();
    }

    public function userLLMChat()
    {

        $userQuery = json_decode($_POST['query'] ?? '{}', true)['user_query'] ?? '';
        
        $response = $this->llmPipeline->generateUserChat($userQuery);

        echo json_encode([
            'success' => true,
            'data' => $response['text'],
            'response_id' => $response['id']
        ]);
    }

    public function submitLLMFeedback()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $responseId = $data['response_id'] ?? null;
        $rating = $data['rating'] ?? null;
        $isHelpful = $data['is_helpful'] ?? null;
        $feedbackText = $data['feedback_text'] ?? null;

        $this->llmPipeline->updateResponseFeedback($responseId, $rating, $isHelpful, $feedbackText);

        echo json_encode([
            'success' => true,
            'message' => 'Feedback submitted successfully'
        ]);
    }
}
