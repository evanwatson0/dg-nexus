<?php 
require_once __DIR__ . '/../bootstrap.php';
require ROOT_PATH . '/authentication/auth_check.php'; 

?>

<!DOCTYPE html>
<!-- 
    Main Page for project

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
    <link rel="stylesheet" href="styling/backbone.css">
    <link rel="stylesheet" href="styling/user_page.css">
    <link rel="stylesheet" href="styling/header.css">

</head>
<body>
    <?php include ROOT_PATH . '/pages/extras/header.php' ?>

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
