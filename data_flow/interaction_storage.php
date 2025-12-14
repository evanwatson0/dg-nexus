<?php 
  /**
   * interaction_storage.php: The Gene-Drug Interaction MySQL Storage 
   * 
   * Author: Evan
   * 
   * This file stores functions to STORE drug-gene interactions 
   * retrieved from the DGI-db API request in the MySQL database
   * 
   * Called in dgi_req.php
   * 
   */
  require __DIR__ . '../db_connect.php';


  /**
   * Main Function to manage the insertion of DGI-db 
   * interactions ($queries) from the GraphQL API request
   * 
   * All other functions in this file are called by this one
   * 
   * @param mixed $queries JSON array
   * @return void
   */
  function insert_relations($queries) {
    $conn = get_connection();

    // iterate through all interactions
    foreach ($queries["nodes"][0]["interactions"] as $query) {

      // if gene name null, ignore
      if (!$query["gene"]["name"]) {
        continue;
      }
      $gene_name = $query["gene"]["name"];
      $gene_longname = $query["gene"]["longName"] ?? null;
      $gene_hgnc = $query["gene"]["conceptId"] ?? null;

      $gene_id = _insert_gene( $conn, $gene_name, $gene_longname, $gene_hgnc);

      $drug_name = $query["drug"]["name"] ?? null;
      $drug_id = _insert_drug($conn, $drug_name);


      // relation columns
      $relation_type = $query["interactionTypes"][0]["type"] ?? 'Unknown/Not Applicable';
      $evidence_score = $query["evidenceScore"] ?? null;
      $interaction_score = $query["interactionScore"] ?? null;
      $drug_specificity = $query["drugSpecificity"] ?? null;
      
      // insert relation
      $interaction_id = _insert_interaction(
        $conn, 
        $drug_id, 
        $gene_id, 
        $relation_type, 
        $evidence_score, 
        $interaction_score, 
        $drug_specificity
      );

      // iterate through the citations
      foreach ($query["publications"] as $publication) {
        $pmid = $publication["pmid"] ?? null;
        $citation = $publication["citation"] ?? null;

        $citation_id = _insert_citations($conn, $pmid, $citation);
        _insert_interaction_citation($conn, $interaction_id, $citation_id);
      }

    }
    
  }


  /**
   * Inserts a drug into the Drug Table in druggene_db
   * If Drug is already in the database, skip insertion
   * 
   * @param mixed $conn 
   * @param mixed $drug_name
   * @return int DrugID, Reference to the drug just inserted/already in database
   */
  function _insert_drug($conn, $drug_name) {
    // Determine there is no duplicate entry for this drug
    $sql = 'SELECT DrugID FROM Drug WHERE DrugName = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $drug_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        return $row["DrugID"];
      }
    }

    // Insert Drug if not already in db
    $sql = 'INSERT INTO DRUG (DrugName) VALUES (?)';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $drug_name);
    $stmt->execute();

    // Return ID of the inserted row
    $drug_id = $stmt->insert_id;
    if (!$drug_id) {
      die("Error: Could not retrieve new InteractionID.");
    }

    return $drug_id;
  }


  /**
   * Inserts a gene into the Gene Table in druggene_db
   * If Gene is already in the database, skip insertion
   * 
   * @param mixed $conn
   * @param mixed $name
   * @param mixed $longname
   * @param mixed $hgnc
   * @return int DrugID, Reference to the drug just inserted/already in database
   */
  function _insert_gene($conn, $name, $longname, $hgnc): String {
    // determine if we have gene entry already (return GeneID of entry if so)
    $sql = 'SELECT GeneID FROM Gene WHERE (GeneSymbol = ? OR GeneSymbol = ?) OR GeneLongName = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $name, $longname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        return $row["GeneID"];
      }
    }

    // perform insertion if gene is not in db
    $sql = "INSERT INTO GENE (GeneSymbol, GeneLongName, HGNC) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $longname, $hgnc);
    $stmt->execute();

    // Get the ID of the inserted row
    $interaction_id = $stmt->insert_id;
    if (!$interaction_id) {
        die("Error: Could not retrieve new InteractionID.");
    }

    return $interaction_id;

  }


  /**
   * Inserts an interaction into the Interaction Table in druggene_db
   * If Interaction between drug_id and gene_id already exists in the database, 
   * skip insertion and return this interaction_id
   * 
   * Params $interaction_score, $evidence_score and $drug_specificity are inserted
   * into the 'Notes' column in the Interaction table
   * 
   * @param mixed $conn
   * @param mixed $drug_id
   * @param mixed $gene_id
   * @param mixed $relation_type
   * @param mixed $evidence_score
   * @param mixed $interaction_score
   * @param mixed $drug_specificity
   * @return int id of the Interaction just inserted/already inserted
   */
  function _insert_interaction($conn, $drug_id, $gene_id, $relation_type, $evidence_score, $interaction_score, $drug_specificity) {
    // check if Interaction already in our database
    $sql = 'SELECT InteractionID FROM Interaction WHERE GeneID = ? AND DrugID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $gene_id, $drug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        return $row["InteractionID"];
      }
    }

    // perform insertion
    $notes = "Interaction Score=$interaction_score; Evidence Score=$evidence_score; Drug Specificity=$drug_specificity";

    // Insert the interaction
    $sql = "INSERT INTO INTERACTION (DrugID, GeneID, RelationType, Notes)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $drug_id, $gene_id, $relation_type, $notes);
    $stmt->execute();

    // Get the ID of the inserted row
    $interaction_id = $stmt->insert_id;
    if (!$interaction_id) {
      die("Error: Could not retrieve new InteractionID.");
    }

    return $interaction_id;
  }


  /**
   * Inserts entry into the Citation table in druggene_db
   * 
   * @param mixed $conn
   * @param mixed $pmid
   * @param mixed $citation
   * @return int CitationID, Reference to the Citation just inserted/already in database
   */
  function _insert_citations($conn, $pmid, $citation) {
    // check if Interaction already in our database
    $sql = 'SELECT CitationID FROM CITATION WHERE Source = ? AND PMID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $citation, $pmid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        return $row["CitationID"];
      }
    }

    $sql = "INSERT INTO CITATION (PMID, Source) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $pmid, $citation);
    $stmt->execute();

    // return the citationID (important for interaction citation)
    $citation_id = $stmt->insert_id;
    if (!$citation_id) {
      die("Error: Could not retrieve new InteractionID.");
    }

    return $citation_id;
  }
  
  
  /**
   * Inserts entry in the Interaction_Citation table in druggene_db
   * 
   * @param mixed $conn
   * @param mixed $interaction_id
   * @param mixed $citation_id
   * @return void
   */
  function _insert_interaction_citation($conn, $interaction_id, $citation_id) {
    // check if Interaction & Citation Link already in our database
    $sql = 'SELECT * FROM INTERACTION_CITATION WHERE InteractionID = ? AND CitationID = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $interaction_id, $citation_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      return;
    }
    
    // If not then add in linkage
    $sql = "INSERT INTO INTERACTION_CITATION (InteractionID, CitationID) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $interaction_id, $citation_id);
    $stmt->execute();

  }

?>