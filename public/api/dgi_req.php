<?php
/**
 * dgi_req.php: The Drug Gene Interaction database (DGI-db) proxy
 * 
 * Author: Evan
 * 
 * Contains the function for retrieving drug-gene interactions from DGI-db.
 * This is done through a GRAPHQL API request.
 * 
 * Supports query by drug and by gene, however at least one of these must be specified
 * (both can't be NULL)
 * 
 */
  function dgi_db_req($gene, $drug) {
    if (!$gene && !$drug) {
      http_response_code(400);
      echo '<br><div> error: Either gene or drug must be provided </div></br>';
      exit;
    }

      // Build GraphQL query
    if ($gene) {
      $query = <<<GRAPHQL
      {
        genes(names: ["$gene"]) {
          nodes {
              interactions {
                  gene {
                      name
                      conceptId
                      longName
                  }
                  drug {
                      name
                  }
                  drugSpecificity
                  evidenceScore
                  interactionScore
                  interactionTypes {
                      type
                      directionality
                  }
                  interactionAttributes {
                      name
                      value 
                  }
                  publications {
                      citation
                      pmid
                  }
              }
          }
        }
      }
      GRAPHQL;
    } elseif ($drug) {
      $query = <<<GRAPHQL
      {
        drugs(names: ["$drug"]) {
          nodes {
            interactions {
                gene {
                    name
                    conceptId
                    longName
                }
                drug {
                    name
                }
                drugSpecificity
                evidenceScore
                interactionScore
                interactionTypes {
                    type
                    directionality
                }
                interactionAttributes {
                    name
                    value 
                }
                publications {
                    citation
                    pmid
                }
            }
          }
        }
      }
      GRAPHQL;
    }

    // Prepare request payload
    $payload = json_encode(["query" => $query]);

    // Init cURL
    $ch = curl_init("https://dgidb.org/api/graphql");

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Content-Length: " . strlen($payload)
        ]
    ]);

    // Execute
    $response = curl_exec($ch);
    // Error handling
    if (curl_errno($ch)) {
        // echo json_encode([
        //     "error" => curl_error($ch)
        // ]);
        echo "error with the curl";
        exit;
    }
    
    // return response
    $response = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      return null;
    }

    if ($gene && isset($response["data"]["genes"])) {
      return $response["data"]["genes"];
    } elseif ($drug && isset($response["data"]["drugs"])) {
      return $response["data"]["drugs"];
    }
  }

?>

