<?php 
  /**
   * llm_request.php: LLM Querying Protocol
   * Author: Evan
   * 
   * Contains the Process for sending and receiving 
   * Utilising OpenAI's LLM API
   * 
   * To effectively run, user must have an OpenAI API key, 
   * stored in their environment under the variable name "OPENAI_API_KEY"
   * 
   * To be called by app.js ONLY in a POST request
   * 
   * Overview of the LLM Protocol
   *  - There are 2 types of requests sent to the LLM, which are 
   *  distinguished by $_POST['query']
   *    
   *    - 'report' :    generates a report outlining notable interactions between drug gene interactions
   *                    as well as citing (existant) relevant literature if available
   *                   
   *                    Interactions are sourced from the DGI db database to ensure authenticity of LLM responses
   * 
   *    - 'user_chat' : Following Report Generations, Users can ask specific and relevant questions concerning the 
   *                    drug gene interactions 
   * 
   * 
   * $_SESSION params
   *    chat_history: contains the chat history which we input into LLM
   *    prompted_user_chat : bool operator tracking if we have loaded the user-chat prompts into chat_history
   * 
   */
  include 'llm_params.php';
  include 'llm_chat_storage.php';

  session_start();
  header('Content-Type: application/json');
  ob_start();
  error_reporting(E_ERROR | E_PARSE); 


  $api_key = getenv("OPENAI_API_KEY");
  $reset = $_POST['reset'] ?? 'true';

  // decode SQL POST Input from ../frontend/app.js post
  $query_json = $_POST['query'] ?? '';
  $query_data = json_decode($query_json, true);
  
  if (json_last_error() !== JSON_ERROR_NONE || !is_array($query_data)) {
   
    ob_clean();
    echo json_encode([
      "success" => false,
      "message" => "Error with the array" . json_last_error_msg()
    ]);
    exit;
  }

  $query_type = $_POST['query_type'] ?? 'report';
  $session_id = $_POST['session_id'] ?? null;


  /* --------------------------------------------------------------
    SESSION RESET LOGIC
  -------------------------------------------------------------- */

  // Reset session if specified
  if ($reset == 'true') {
    session_destroy();
    session_start();
    $_SESSION['chat_history'] = $initialisation_prompts;
    $_SESSION['prompted_user_chat'] = false;

  }

  // Initialise LLM w/ prompts
  if (!isset($_SESSION['chat_history']) || !is_array($_SESSION['chat_history']) || empty($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = $initialisation_prompts;
    $_SESSION['prompted_user_chat'] = false;
  }


  /* --------------------------------------------------------------
    SEND TO LLM (via reusable function)
  -------------------------------------------------------------- */

  // depending on the type of request we want, we make the query
  if ($query_type == 'report') {
    $query = make_report_prompt($query_data) ?? '';
    
  } else if ($query_type == 'user_chat') {

    // Load Prompt Engineering onto session (from llm_params.php)
    if (!$_SESSION['prompted_user_chat']) {
      $_SESSION['chat_history'][] = $user_chat_system;
      $_SESSION['chat_history'][] = $user_chat_user;
      $_SESSION['chat_history'][] = $user_chat_assistant;
      $_SESSION['prompted_user_chat'] = true;
    }
    
    $query = make_user_query_prompt($query_data['user_query']);
  }


  $_SESSION['chat_history'][] = [
    "role" => "user",
    "content" => [
      ["type" => "text", "text" => $query]
    ]
  ];

  $reply = send_to_llm($_SESSION['chat_history'], $model, $temperature, $api_key);
  
  
  // Add query to current session & Save it to DB!
  $_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $reply];



  $prompt_id = insert_prompt(null, $session_id, $query);
  $response_id = insert_llm_response(null, $prompt_id, $reply, null, null, null);

  // Success!
  ob_clean();
  echo json_encode([
    "success" => true,
    "message" => "Matches found",
    "data" => $reply,
    "response_id" => $response_id
  ]);
  exit;

  function send_to_llm($messages, $model, $temperature, $api_key) {
    $payload = json_encode([
      'model' => $model,
      'messages' => $messages,
      'temperature' => $temperature
    ]);

    // Send Request to OpenAI
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer ' . $api_key,
      'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    // Parse and store assistant reply + error checking
    if (curl_errno($ch)) {
      ob_clean();
      echo json_encode([
        "success" => false,
        "message" => 'Curl error: ' . curl_error($ch)
      ]);
      exit;
    
    }

    if (!$response) {
      ob_clean();
      echo json_encode([
        "success" => false,
        "message" => 'Empty response from OpenAI.'
      ]);
      exit;
    }

    $result = json_decode($response, true);

    // error check
    if (json_last_error() !== JSON_ERROR_NONE) {
      ob_clean();
      echo json_encode([
        "success" => false,
        "message" => "OpenAI response JSON error: " . json_last_error_msg() . "\nRaw: " . $response
      ]);
      exit;

    }


    $reply = $result['choices'][0]['message']['content'] ?? 'No response received from ChatGPT.';
    return $reply;

  }

  function make_user_query_prompt($user_query): string {
    $query_string = "
      As a biomedical and clinical expert, pretend you will be asked a user
      submitted query concerning data your report has generated. Return a concise,
      1 paragraph explanation to the user submitted query, looking online and providing
      relevant readings ONLY if it can be found. 
      explanation in response to a user submitted query. Ensure question is relevant
      to your position as an expert. Give only the answer to the user query in your response.

      The user query is: 
    " . $user_query;

    return $query_string;
  }

  /**
   * Parses Query Data into a string input used to send to ChatGPT
   * @param array $query_data
   * @return string
   */
  function make_report_prompt(array $query_data): string {
    
    $prompt_string = "Generate a report for the following interactions using the mentioned structure. The queries are as follows: \n";

    $query_string = '';
    foreach ($query_data as $entry) {
      $gene = $entry['gene'] ?? '';
      $drug = $entry['drug'] ?? '';
      $relation_type = $entry['relation'] ?? '';
      $note = $entry['notes'] ?? '';
      $source = $entry['source'] ?? '';

      $query_string .= "Gene: $gene, Drug: $drug, Relation Type: $relation_type, Sources $source, Additional Notes: $note\n";
    }

    return $prompt_string . $query_string;
  }




?>