<?php
require_once __DIR__ . '/../../bootstrap.php';
// require ROOT_PATH . '/app/auth/auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DG Nexus</title>

    <link rel="stylesheet" href="styling/backbone.css">
    <link rel="stylesheet" href="styling/header.css">
    <link rel="stylesheet" href="styling/dashboard.css">

    <style>
        /* Simple tab styling */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ccc;
            margin-bottom: 8px;
        }
        .tab {
            padding: 8px 16px;
            cursor: pointer;
            border: 1px solid #ccc;
            border-bottom: none;
            background: #f9f9f9;
            margin-right: 4px;
        }
        .tab.active {
            background: #fff;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>

<body>
    <?php include ROOT_PATH . '/public/pages/elements/header.php' ?>

    <main>

        <img src="styling/logo.png" class="center-img" alt="Centered">
        <p class="muted">Enter a gene or drug and choose the interaction type. You can fill either field.</p>
        
        <!-- Search Form -->
        <form id="gdi-search-form" class="card" method="POST">
            <div>
                <input type="text" id="input" name="input" placeholder="Insert Gene or Drug Here e.g., CYP2D6" />
            </div>

            <div>
                <label for="gene_or_drug">Gene Or Drug?</label>
                <select id="gene_or_drug" name="gene_or_drug">
                    <option value="gene">Gene</option>
                    <option value="drug">Drug</option>
                </select>
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

            <div class="full btns">
                <button type="submit" name="action" value="query">Search</button>
            </div>
        </form>

        <!-- Interaction Search Status -->
        <div id="gdi-search-status" class="section"></div>

        <!-- First tab group -->
        <div class="section">
            <div class="tabs" data-group="data-tabs">
                <div class="tab active" data-tab="structured-data">Structured Data</div>
                <div class="tab" data-tab="visualisation">Visualisation</div>
            </div>

            <div id="structured-data" class="tab-content active" data-group="data-tabs">
                <h3>Structured Data</h3>
                <div class="card">
                    <div class="table-wrapper">
                        <div id="table">
                            <div class="muted">Form generated after searching</div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="visualisation" class="tab-content" data-group="data-tabs">
                <h3>Visualisation</h3>
                <div id="viz" class="card">
                    <svg id="graph"></svg>
                    <div class="muted">Tip: Drag nodes to move them around</div>
                </div>
            </div>
        </div>

        <!-- Second tab group -->
        <div class="section">
            <h3>LLM Analysis</h3>

            <div class="tabs" data-group="llm-tabs">
                <div class="tab active" data-tab="report">Report</div>
                <div class="tab" data-tab="chat">Chat</div>
            </div>

            <div class="card">

                <!-- LLM REPORT GENERATION -->
                <div id="report" class="tab-content active" data-group="llm-tabs">
                    <div class="btns" style="margin-bottom:8px;">
                        <button type="button" id="generate-llm-btn" disabled>Generate Report</button>
                        <button type="button" id="save-llm-btn" disabled>Save Report</button>
                    </div>
                    <p id="llm-report-output"  class="muted">Report will generate here...</p>
                </div>

                <!-- LLM CHAT GENERATION -->
                <div id="chat" class="tab-content" data-group="llm-tabs">
                    <form class="single-column-form" id="llm-chat-form">
                        <label for="llm-chat-input">Ask a follow up question</label>
                        <input type="text" id="llm-chat-input" name="llm-chat-input"
                            style="width:100%; padding:8px; font-size:14px;"
                            placeholder="e.g. Can you explain why CYP2D6 inhibitors are clinically important?">

                        <div class="btns" style="margin-top:8px;">
                            <button type="submit" id="llm-chat-button" disabled>Ask</button>
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

           
        </div>


        <!-- Transcript Section -->
        <div class="section">
            <h3>Transcript</h3>
            <div id="transcript" class="card">
                <p class="muted">Transcript of interactions will appear here...</p>
            </div>
            <div class="btns" style="margin-top:8px;">
                <button type="button" id="save-pdf-btn">Save PDF</button>
            </div>
        </div>

    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/markdown-it/13.0.2/markdown-it.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="module" src="app.js" defer></script>

    <script>
        // Scoped tab switching per group
        document.querySelectorAll('.tabs').forEach(tabGroup => {
            const groupName = tabGroup.dataset.group;
            tabGroup.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    // deactivate all tabs in this group
                    tabGroup.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                    document.querySelectorAll(`.tab-content[data-group="${groupName}"]`).forEach(c => c.classList.remove('active'));
                    // activate clicked tab and its content
                    tab.classList.add('active');
                    document.getElementById(tab.dataset.tab).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>