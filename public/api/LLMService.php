<?php 

  namespace App\Services;
  use App\Models\LLMParams;
class LLMService {


  public static function send_to_llm($messages) {
    $payload = json_encode([
      'model' => LLMParams::getModel(),
      'messages' => $messages,
      'temperature' => LLMParams::getTemperature()
    ]);

    // Send Request to OpenAI
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer ' . LLMParams::getOpenAIKey(),
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



}

