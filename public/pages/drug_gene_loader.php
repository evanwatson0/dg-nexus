<?php


  require_once __DIR__ . '/../../bootstrap.php';

  // require ROOT_PATH . '/app/auth/auth_check.php';
  // include ROOT_PATH . '/data_flow/interaction_storage.php';
  // include ROOT_PATH . '/data_flow/dgi_req.php';

  include ROOT_PATH . '/app/Services/GeneDrugDataPipeline.php';

use App\Services\DGI_DB_API;
use App\Services\GeneDrugDataPipeline;

  // if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  //   http_response_code(405);
  //   exit('Method Not Allowed');
  // }

  // generate new gene drug pipeline
  $pipeline = new GeneDrugDataPipeline();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gene = $_POST['gene'] ?? Null;
    $drug = $_POST['drug'] ?? Null;

    if ($gene) {
      $payload = DGI_DB_API::request_gene($gene);
    } else if ($drug) {
      DGI_DB_API::request_drug($drug);
    }
    // retrieve the API stuff

    if (!$payload) {
      http_response_code(405);
      exit('Returned Empty Array');
    }

    // insert into 
    $pipeline->insertRelations($payload);
  }
?>


<!DOCTYPE html>
<!-- 
  Auxiliary Page 

  Primary Author: Evan

  This file provides the primary UI layout for retrieving drugs and genes
  from Drug
  
-->
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Drug Gene Loader</title>


  <!-- Styling resources for Header -->
  <link rel="stylesheet" href="assets/css/backbone.css">
  <link rel="stylesheet" href="assets/css/drug_gene_loader.css">
  <link rel="stylesheet" href="assets/css/header.css">

</head>

<body>
  <?php include ROOT_PATH . '/public/pages/elements/header.php' ?>

  <main>
    <h2>Drug-Gene Relation Loader</h2>
    <p class="muted">Search for drug-gene pairs and load interaction data into the database.</p>

    <form id="search-form" class="card" method="POST">
      <div>
        <label for="gene">Gene</label>
        <input type="text" id="gene" name="gene" placeholder="e.g., CYP2D6" />
      </div>
      <div>
        <label for="drug">Drug</label>
        <input type="text" id="drug" name="drug" placeholder="e.g., Fluoxetine" />
      </div>

      <div class="full btns">
        <button type="submit">Search</button>
      </div>
    </form>
  </main>


</body>
</html>
