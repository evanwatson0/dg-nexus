<?php
  /**
   * interaction_retrieval: Enables the retrieval of drug gene
   * interactions
   * 
   * Author: Evan
   * 
   * Called by app.js 
   * upon Submission of the Drug Gene Interaction Form on ../frontend/user_page.php
   * We retrieve entries from the MySQL db (this file) to prompt the LLM
   * 
   */
  require_once __DIR__ . '/../bootstrap.php';
  include ROOT_PATH . '/db_connect.php';

  header('Content-Type: application/json');

  // capture accidental output & hide notices/warnings
  ob_start(); 
  error_reporting(E_ERROR | E_PARSE);

  // decode PARAM post
  $gene = $_POST['gene'] ?? '';
  $drug = $_POST['drug'] ?? '';
  $relation_type = $_POST['relation_type'] ?? '';


  query_db($gene, $drug, $relation_type);

  /**
   * Retrieves all tuples matching the gene, drug and relation of interest.
   * If the any of these params are empty, they will have a wildcard match instead
   * Prerequisites: Gene and Drug both can't be NULL!
   * 
   * Outputs JSON object, the return["data"] contains the following fields
   * - GeneSymbol
   * - GeneLongName
   * - DrugName
   * - RelationType
   * - Notes
   * - Citations
   * 
   * 
   * @param mixed $gene
   * @param mixed $drug
   * @param mixed $relation_type
   * @return void
   */
  function query_db($gene, $drug, $relation_type) {
    // 先把外部資料寫進 DB
    // dgi_db_req($gene, $drug);
    // Don't query db is gene AND drug are both EMPTY
    if ($gene == '' && $drug == '') {
      // do nothing - can't search for everything!
      ob_clean();
      echo json_encode([
          "success" => false,
          "message" => "Cannot search with empty gene AND drug.",
          "data" => []
      ]);
      return;
    }

    // Replace variables with a universal String Matcher if gene xor drug is blank
    if ($gene == '') {
      $gene = '.*';
    }
    if ($drug == '') {
      $drug = '.*';
    }
    if ($relation_type == "") {
      $relation_type = '.*';
    }

    $conn = get_connection();

    $sql = "
      SELECT DISTINCT
          g.GeneSymbol,
          g.GeneLongName,
          d.DrugName,
          i.RelationType,
          i.Notes,
          GROUP_CONCAT(CONCAT(c.Source, ' (PMID:', c.PMID, ')') SEPARATOR '; ') AS Citations
      FROM INTERACTION i
      LEFT JOIN GENE g ON g.GeneID = i.GeneID
      LEFT JOIN DRUG d ON d.DrugID = i.DrugID
      LEFT JOIN INTERACTION_CITATION ic ON ic.InteractionID = i.InteractionID
      LEFT JOIN CITATION c ON c.CitationID = ic.CitationID
      WHERE (g.GeneSymbol REGEXP ? OR g.GeneLongName REGEXP ?)
        AND d.DrugName REGEXP ?
        AND i.RelationType REGEXP ?
        AND NOT d.DrugName REGEXP 'Chembl'
      GROUP BY 
          i.InteractionID,
          g.GeneSymbol,
          g.GeneLongName,
          d.DrugName,
          i.RelationType,
          i.Notes;
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $gene, $gene, $drug, $relation_type);
    $stmt->execute();
    $result = $stmt->get_result();

    // if no matches
    if ($result->num_rows == 0) {
      ob_clean();
      echo json_encode([
          "success" => false,
          "message" => "No matches found",
          "data" => []
      ]);
      exit;
    }

    $return_arr = [];
    while ($row = $result->fetch_assoc()) {
      $return_arr[] = $row; // or specific columns
    }

    ob_clean();
    echo json_encode([
        "success" => true,
        "message" => "Matches found",
        "data" => $return_arr
    ]);
    exit;
  }

