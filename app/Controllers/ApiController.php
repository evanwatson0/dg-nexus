<?php
namespace App\Controllers;


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
        $this->geneDrugPipeline = new GeneDrugDataPipeline($this->conn);
        $this->llmPipeline = new LLMDataPipeline($this->conn);
    }

    /* -------------------- Interactions -------------------- */

    // public function queryInteractions()
    // {
    //     $data = $_POST;
    //     $gene = $data['gene'] ?? '';
    //     $drug = $data['drug'] ?? '';
    //     $relation_type = $data['relation_type'] ?? '';

    //     $results = $this->geneDrugPipeline->queryDb($gene, $drug, $relation_type);

    //     echo json_encode([
    //         'success' => true,
    //         'data' => $results
    //     ]);
    // }

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
        $query_json = $_POST['query'] ?? '';
        $query_data = json_decode($query_json, true);



        $response = $this->llmPipeline->generateReport($query_data);

        echo json_encode([
            'success' => true,
            'data' => $response['text'],
            'response_id' => $response['id']
        ]);
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
