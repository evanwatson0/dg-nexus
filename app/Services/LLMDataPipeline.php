<?php 

  /**
   * llm_chat_stoage.php
   * Author: Evan
   * 
   * This File encodes functionality for the storage of LLM based elements in the MySQL database, 
   * including Sessions, User Prompts, LLM Responses & User Feedback. Each storage method is 
   * enclosed through a specific function
   * 
   * Use 
   * - 'app.js'
   * Data is POST-ed to this file as a JSON formatted string
   * Each POST will contain data used to run different functions
   * The specific function will be specified in data['action']
   * 
   * - '../llm/llm_request.php'
   * Functions are called directly (without a POST request) from the file itself
   */
	// include '../db_connect.php';
  
  // $conn = get_connection();

  // // Decode POST data
  // header('Content-Type: application/json; charset=utf-8');
  // $data = json_decode(file_get_contents("php://input"), true);
  // $action = $data['action'] ?? null;

  // // 'action' parameter determines the specific function and parameters to use 
  // switch ($action) {
  //   case 'create_session':
  //     $user_identifier = $data['user_identifier'] ?? null;
  //     $id = create_session($conn, $user_identifier);
  //     echo json_encode(["id" => $id]);
  //     break;

  //   case 'name_session':
  //     $session_id = $data['session_id'] ?? null;
  //     $name = $data['name'] ?? null;
  //     name_session($conn, $session_id, $name);
  //     break;

  //   case 'retrieve_session':

  //     $session_name = $data['session_name'] ?? null;
  //     $id = retrieve_session($conn, $session_name);
  //     echo json_encode(["id" => $id]);
  //     break;

  //   case 'insert_prompt':
  //     $session_id = $data['session_id'] ?? null;
  //     $prompt_text = $data['prompt_text'] ?? null;
  //     $id = insert_prompt($conn, $session_id, $prompt_text);
  //     echo json_encode(["id" => $id]);
  //     break;

  //   case 'insert_response':
  //     $prompt_id = $data['prompt_id'] ?? null;
  //     $response = $data['response'] ?? null;
  //     $session_id = $data['response_id'] ?? null;
  //     $rating = $data['rating'] ?? null;
  //     $is_helpful = $data['is_helpful'] ?? null;
  //     $feedback_text = $data['feedback_text'] ?? null;
      
  //     insert_llm_response($conn,  $prompt_id, $response,$rating, $is_helpful, $feedback_text);
  //     break;

  //   case 'add_feedback':
      
  //     $response_id = $data['response_id'] ?? null;
  //     $rating = $data['rating'] ?? null;
  //     $is_helpful = $data['is_helpful'] ?? null;
  //     $feedback_text = $data['feedback_text'] ?? null;
  //     update_llm_response($conn, $response_id, $rating, $is_helpful, $feedback_text);
  // }



namespace App\Services;

require_once __DIR__ . '/../../bootstrap.php';
require ROOT_PATH . '/app/Models/LLMParams.php';
require_once ROOT_PATH . '/config/db_connect.php';

use App\Models\LLMParams;
use mysqli;


class LLMDataPipeline
{
    private mysqli $conn;
    private int $current_session_id;

    private $chat_history;

    private $recentReport;
  



    // TODO: create SESSION when Constructing LLMDataPipeline
    public function __construct()
    {
      $this->conn = get_connection();
      $this->chat_history = LLMParams::getInitialisationPrompts();
    }

    /* --------------------------------------------------------------
       LLM SESSION MANAGEMENT
    -------------------------------------------------------------- */
    public function createLLMSession(?string $userIdentifier): void
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO llm_session (UserIdentifier) VALUES (?)'
        );
        $stmt->bind_param('s', $userIdentifier);
        $stmt->execute();

        $this->current_session_id = $stmt->insert_id;
    }

    public function nameLLMSession(string $name): void
    {
        $stmt = $this->conn->prepare(
            'UPDATE llm_session SET SessionName = ? WHERE SessionID = ?'
        );
        $stmt->bind_param('si', $name, $this->current_session_id);
        $stmt->execute();
    }

    public function retrieveLLMSessionByName(string $sessionName): bool
    {
        $stmt = $this->conn->prepare(
            'SELECT SessionID FROM llm_session WHERE SessionName = ?'
        );
        $stmt->bind_param('s', $sessionName);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $this->current_session_id = (int)$row['SessionID'];
            return true;
        }

        return false;
    }


    /* --------------------------------------------------------------
       LLM request
    -------------------------------------------------------------- */
    public function generateReport($query_data, $input, $type, $relation_type): string {

        $prompt_string = LLMParams::make_report_prompt($query_data, $input, $type, $relation_type);

        $this->chat_history[] = [
            "role" => "user",
            "content" => [
            ["type" => "text", "text" => $prompt_string]
            ]
        ];

        // send to LLM and Receive the reply
        $llm_response = LLMService::send_to_llm($this->chat_history);

        // add the reply
        $this->chat_history[] =  ['role' => 'assistant', 'content' => $llm_response];
        
        
        $prompt_id = $this->insertPrompt($prompt_string);
        $this->insertResponse($prompt_id, $llm_response);

        $this->recentReport = $llm_response;
        return $llm_response;
    }

    // TODO: change it to use SQL instead
    public function getMostRecentReport(): string {
        // $stmt = $this->conn->prepare(
        //     'SELECT * FROM Report '
        // )
        return $this->recentReport;
    }


    public function generateUserChat($user_query) {
        $prompt_string = LLMParams::make_user_query_prompt($user_query);

        $this->chat_history[] = [
            "role" => "user",
            "content" => [
            ["type" => "text", "text" => $prompt_string]
            ]
        ];

        // send to LLM and Receive the reply
        $llm_response = LLMService::send_to_llm($this->chat_history);

        // add the reply
        $this->chat_history[] =  ['role' => 'assistant', 'content' => $llm_response];
        
        
        $prompt_id = $this->insertPrompt($prompt_string);
        $this->insertResponse($prompt_id, $llm_response);

        return $llm_response;
    }

    /* --------------------------------------------------------------
       STORING PROMPTS & RESPONSES
    -------------------------------------------------------------- */
    public function insertPrompt(string $promptText): int
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO llm_prompt (SessionID, PromptText) VALUES (?, ?)'
        );
        $stmt->bind_param('is', $this->current_session_id, $promptText);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function insertResponse(
        int $promptId,
        string $response,
        ?int $rating = null,
        ?bool $isHelpful = null,
        ?string $feedbackText = null
    ): int {
        $stmt = $this->conn->prepare(
            'INSERT INTO llm_response 
             (PromptID, ResponseText, Rating, IsHelpful, FeedbackText)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'isiis',
            $promptId,
            $response,
            $rating,
            $isHelpful,
            $feedbackText
        );
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function updateResponseFeedback(
        int $responseId,
        ?int $rating,
        ?bool $isHelpful,
        ?string $feedbackText
    ): void {
        $stmt = $this->conn->prepare(
            'UPDATE llm_response
             SET Rating = ?, IsHelpful = ?, FeedbackText = ?
             WHERE ResponseID = ?'
        );
        $stmt->bind_param(
            'iisi',
            $rating,
            $isHelpful,
            $feedbackText,
            $responseId
        );
        $stmt->execute();
    }
}




















 
  // function create_session($conn, $user_identifier) {
  //   $sql = 'INSERT INTO llm_session (UserIdentifier) VALUES (?)';
  //   $stmt = $conn->prepare($sql);
  //   $stmt->bind_param('s', $user_identifier);
  //   $stmt->execute();

  //   $session_id = $stmt->insert_id;
  //   if (!$session_id) {
  //     die("Error: Could not create a new session.");
  //   }

  //   return $session_id;
  // }

  // /**
  //  * Given the session_id, allows the user to save their session under 
  //  * a specific name 
  //  * 
  //  * @param mixed $conn
  //  * @param mixed $session_id
  //  * @param mixed $name
  //  * @return void
  //  */
  // function name_session($conn, $session_id, $name) {
  //   $sql = 'UPDATE session_id SET SessionName = ? WHERE SessionID = ?';
  //   $stmt = $conn->prepare($sql);
  //   $stmt->bind_param('si', $name, $session_id);
  //   $stmt->execute();
  // }

  // /**
  //  * When user wants to retrieve session the used previously
  //  * via the name they gave their session
  //  * 
  //  * @param mixed $session_id
  //  * @param mixed $session_name
  //  */
  // function retrieve_session($conn, $session_name) {
  //   // identify and return session through id or name
    
  //   if ($session_name) {
  //     $sql = 'SELECT SessionID FROM llm_session WHERE SessionName = ?';
  //     $stmt = $conn->prepare($sql);
  //     $stmt->bind_param('s', $session_name);
  //     $stmt->execute();

      
  //     $result = $stmt->get_result();
  //     if ($result->num_rows > 0) {
  //       while ($row = $result->fetch_assoc()) {
  //         return $row["SessionID"];
  //       }
  //     } else {
  //       die('Error: could not retrieve user session!');
  //     }
  //   }
  // }

  // /**
  //  * Inserts a prompt asked during a session
  //  * 
  //  * @param mixed $conn
  //  * @param mixed $session_id
  //  * @param mixed $text the prompt 
  //  */
	// function insert_prompt($conn, $session_id, $text) {
  //   if (!$conn) {
  //     $conn = get_connection();
  //   }

  //   $sql = 'INSERT INTO llm_prompt (SessionID, PromptText) VALUES (?, ?)';
  //   $stmt = $conn->prepare($sql);
  //   $stmt->bind_param('is', $session_id, $text);
  //   $stmt->execute();

  //   // Get the ID of the inserted row
  //   $prompt_id = $stmt->insert_id;

  //   if (!$prompt_id) {
  //     die("Error: Could not retrieve new InteractionID.");
  //   }

  //   return $prompt_id;
	// }

  // /**
  //  * Inserts the response of the LLM to the give prompt (prompt_id)
  //  * 
  //  * @param mixed $conn
  //  * @param mixed $prompt_id
  //  * @param mixed $response
  //  * @param mixed $rating
  //  * @param mixed $is_helpful
  //  * @param mixed $feedback_text
  //  */
  // function insert_llm_response($conn, $prompt_id, $response, $rating, $is_helpful, $feedback_text) {
  //   if (!$conn) {
  //     $conn = get_connection();
  //   }
  //   $sql = 'INSERT INTO llm_response (PromptID, ResponseText, Rating, IsHelpful, FeedbackText) VALUES (?, ?, ?, ?, ?)';

  //   $stmt = $conn->prepare($sql);
  //   $stmt->bind_param('isiis', $prompt_id, $response, $rating, $is_helpful, $feedback_text);
  //   $stmt->execute();

  //   $resp_id = $stmt->insert_id;

  //   if (!$resp_id) {
  //     die("Error: Could not retrieve new InteractionID.");
  //   }

  //   return $resp_id;

  // }

  // /**
  //  * Updates the response given by the LLM with feedback inputted by the user 
  //  * 
  //  * @param mixed $conn
  //  * @param mixed $id Unique identifier of the llm_response tuple
  //  * @param mixed $rating
  //  * @param mixed $is_helpful
  //  * @param mixed $feedback_text
  //  * @return void
  //  */
  // function update_llm_response($conn, $id, $rating, $is_helpful, $feedback_text) {
  //   if (!$conn) {
  //     $conn = get_connection();
  //   }
  //   $sql = 'UPDATE llm_response SET Rating = ?, IsHelpful = ?, FeedbackText = ? WHERE ResponseID = ?';

  //   $stmt = $conn->prepare($sql);
  //   $stmt->bind_param('iisi', $rating, $is_helpful, $feedback_text, $id);
  //   $stmt->execute();

  // }

