<?php
session_start();

if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<!-- 
    Main Page for project

    Primary Author: Claire

    This file provides the primary UI layout for thIS application.
    Only static HTML and structural components are defined here.
    All dynamic logic (form actions, DOM updates, API requests, etc.)
    can be found in app.js.
-->
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8" />
    <title>DG Nexus Main Page</title>

    <!-- Styling resources for Header -->
    <link rel="stylesheet" href="./styling/header.css">

    <style>
   

        body { 
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Noto Sans TC, Arial; 
            margin: 0; 
            line-height: 1.5; 
            background: #f3f4f6;

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

        .section h3 {
            margin-top: 0;
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

        input, select, button { 
            padding: 8px; 
            font-size: 14px; 
        }

        input, select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
        }

        input:focus, select:focus {
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

        button:disabled {
            background: #9ca3af;
            cursor: default;
        }

        button:not(:disabled):hover {
            background: #000000;
        }

        .full { grid-column: 1 / -1; }
        .section { margin-top: 24px; }

        .card { 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 16px; 
            white-space: pre-line;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
        }

        table { 
            border-collapse: collapse; 
            width: 100%;
        }
      
        /* NEW — scrollable table wrapper */
        .table-wrapper {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 4px;
            background: #fff;
        }

        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .table-wrapper th,
        .table-wrapper td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
            word-wrap: break-word;
        }

        th, td { 
            border-bottom: 1px solid #eee; 
            padding: 8px; 
            text-align: left; 
        }

        th { 
            background: #fafafa; 
            font-weight: 600; 
        }

        #viz svg { 
            width: 100%; 
            height: 360px; 
            border: 1px dashed #e5e7eb; 
            border-radius: 12px; 
            background: #fcfcfd; 
        }

        .muted { 
            color: #6b7280; 
            font-size: 14px; 
            overflow-y:auto;   
            max-height: 40vh; 
        }

        .row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 12px; 
        }

        .btns { 
            display: flex; 
            gap: 8px; 
        }

        .llm-chat-input-area {
            margin-top: 20px; 
            width: 80%;
            display: flex; 
            gap: 10px; 
            color: #333; 
        }

        /* feedback */
       
        #user-feedback.single-column-form,
        #llm-chat-form.single-column-form {
            display: flex !important;
            flex-direction: column !important;
            gap: 14px !important;
            max-width: 720px;
        }

        /* Also override row containers inside them */
        #user-feedback .row,
        #llm-chat-form .row {
            display: flex !important;
            flex-direction: column !important;
            gap: 12px;
        }

        /* Buttons area stays horizontal */
        .single-column-form .btns {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        
        .center-img {
            display: block;
            margin: 0 auto;
        }

    </style>
</head>
<body>
    <?php include './header.php' ?>

    <main>

        <img src="styling/logo.png" class="center-img" alt="Centered">
        <p class="muted">Enter a gene or drug and choose the interaction type. You can fill either field.</p>
        
        <!-- Search Form 
        Where user enters gene,drug, relation type to search database
        You have the option to generate an LLM Report as well 
        -->

        <form id="search-form" class="card" method="POST">
            <div>
                <label for="gene">Gene</label>
                <input type="text" id="gene" name="gene" placeholder="e.g., CYP2D6" />
            </div>
            <div>
                <label for="drug">Drug</label>
                <input type="text" id="drug" name="drug" placeholder="e.g., Fluoxetine" />
            </div>

            <div>
                <label for="relation_type">Relation type</label>
                <select id="relation_type" name="relation_type">
                    <option value="">— Any —</option>
                    <option value="inhibitor">Inhibitor</option>
                    <option value="upregulator">Upregulator</option>
                    <option value="snp_specific">SNP-specific response</option>
                    <option value="vaccine">Vaccine</option>
                    <option value="negative modulator">Negative modulator</option>
                </select>
            </div>

            <div>
                <label for="want_llm">LLM Report</label>
                <select id="want_llm" name="want_llm">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>

            <div class="full btns">
                <button type="submit" name="action" value="query">Search</button>
            </div>
        </form>

        <!-- Interaction Search Status
        This holds the text for the Status output of the Interaction Search  
        It can say Loading, Error, or Done 
        -->

        <div id="status" class="section"></div>

        <!-- LLM Output Section -->
        <div class="section">
            <h3>LLM Report Transcript</h3>
            <div id="llm" class="card">Not generated yet</div>


            
            <div class="btns" style="margin-top:8px;">
                <button type="button" id="save-llm-btn" disabled onclick="">
                    Save LLM Report
                </button>
            </div>
        </div>

        <!-- Structure Data Return -->
        <div class="section">
            <h3>Structured Data</h3>
            <div class="card">
                <div class="table-wrapper">
                    <div id="table">
                        <div class="muted">Form generated after searching</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
        <h3>Visualisation</h3>
        <div id="viz" class="card">
            <svg id="graph"></svg>
            <div class="muted">Tip: Drag nodes to move them around</div>
        </div>
        </div>

<div class="section" id="feedback-section">
    <h3>User feedback</h3>
    <form id="user-feedback" class="card single-column-form">
        <div class="row">
            <div>
                <label for="feedback-rating">Overall rating</label>
                <select id="feedback-rating" name="feedback-rating">
                    <option value="">Please select</option>
                    <option value="5">5 - Very useful</option>
                    <option value="4">4 - Useful</option>
                    <option value="3">3 - Neutral</option>
                    <option value="2">2 - Not very useful</option>
                    <option value="1">1 - Not useful</option>
                </select>
            </div>
        </div>

        <div class="section">
            <label for="feedback-comment">Comments</label>
            <textarea id="feedback-comment" name="feedback-comment" rows="4"
            style="width:100%; padding:8px; font-size:14px; box-sizing:border-box;"
            placeholder="e.g. The table is clear, but I want to see more clinical interpretation."></textarea>

        </div>

        <div class="btns" style="margin-top:8px;">
            <button type="submit" id="feedback-submit" disabled onclick="">Submit feedback</button>
            <span id="feedback-status" class="muted"></span>
        </div>
    </form>

    
    <div class="section">
        <h3>Discuss with AI assistant</h3>
        <form class="card single-column-form" id="llm-chat-form">
            <label for="llm-chat-input">Ask a follow up question</label>
            <input type="text" id="llm-chat-input" name="llm-chat-input"
                style="width:100%; padding:8px; font-size:14px;"
                placeholder="e.g. Can you explain why CYP2D6 inhibitors are clinically important?">

            <div class="btns" style="margin-top:8px;">
                <button type="submit" id="llm-chat-button" disabled onclick="">Ask</button>
            </div>
        </form>

        <div id="chat-log" class="card"
            style="max-height:260px; overflow-y:auto; margin-top:12px;">
            <div id="llm-chat-response-section" class="muted">
                Your response generates here!
            </div>
        </div>

    </div>
</div>

    </main>

    <!-- Markdown parser -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/markdown-it/13.0.2/markdown-it.min.js"></script>

    <!-- jsPDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="module" src="app.js" defer></script>

</body>
</html>
