<?php require '../authentication/auth_check.php'; ?>

<?php 
  include '../../data_flow/interaction_storage.php';
  include '../../data_flow/dgi_req.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gene = $_POST['gene'];
    $drug = $_POST['drug'];
    dgi_db_req($gene, $drug);
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
    <link rel="stylesheet" href="../styling/header.css">

    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Noto Sans TC, Arial;
            margin: 0;
            line-height: 1.5;
            background-image: url('styling/background.jpg');
            background-repeat: no-repeat; 
            background-size: cover; 
            background-position: center;
            background-attachment: fixed;
        }

        main {
            max-width: 1100px;
            margin: 40px auto;
            padding: 24px 28px 32px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }

        h2 {
            margin-top: 0;
            margin-bottom: 4px;
            font-size: 26px;
        }

        p.muted {
            color: #6b7280;
            font-size: 13px;
        }

        form {
            display: grid;
            gap: 12px;
            max-width: 720px;
            grid-template-columns: 1fr 1fr;
        }

        label {
            font-size: 14px;
            color: #333;
        }

        input,
        button {
            padding: 8px;
            font-size: 14px;
        }

        input {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            width: 100%;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.20);
        }

        button {
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: #111827;
            color: #ffffff;
            font-weight: 500;
        }

        button:hover {
            background: #000000;
        }

        .full { grid-column: 1 / -1; }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        .btns {
            display: flex;
            gap: 8px;
        }
    </style>

    


</head>

<body>
  <?php include '../extras/header.php' ?>

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
