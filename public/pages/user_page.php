<?php
require_once __DIR__ . '/../../bootstrap.php';
require ROOT_PATH . '/app/auth/auth_check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DG Nexus</title>

    <link rel="stylesheet" href="styling/backbone.css">
    <link rel="stylesheet" href="styling/header.css">
    <link rel="stylesheet" href="styling/dashboard.css">
</head>
<body>

<?php include ROOT_PATH . '/pages/extras/header.php'; ?>

<main class="container">

    <!-- ================= SEARCH ================= -->
    <section class="search-card card">
        <h2>Search Drug–Gene Interactions</h2>

        <input
            type="text"
            id="search-input"
            placeholder="Enter a gene or drug (e.g. CYP2D6 or Fluoxetine)"
        />

        <div class="checkbox-group">
            <label><input type="checkbox" value="inhibitor"> Inhibitor</label>
            <label><input type="checkbox" value="activator"> Activator</label>
            <label><input type="checkbox" value="substrate"> Substrate</label>
        </div>

        <button id="search-btn">Search</button>

        <div id="status" class="muted"></div>
    </section>

    <!-- ================= TABS ================= -->
    <section class="tabs-section">

        <div class="tabs">
            <button class="tab active" data-tab="table">Table</button>
            <button class="tab" data-tab="graph">Graph</button>
            <button class="tab" data-tab="llm">LLM Report</button>
            <button class="tab" data-tab="chat">Chat</button>
        </div>

        <!-- ================= TAB CONTENT ================= -->

        <!-- TABLE -->
        <div class="tab-content active" id="tab-table">
            <div class="card" id="table">Search results appear here</div>
        </div>

        <!-- GRAPH -->
        <div class="tab-content" id="tab-graph">
            <div class="card">
                <svg id="graph"></svg>
                <p class="muted">Drag nodes to explore relationships</p>
            </div>
        </div>

        <!-- LLM REPORT -->
        <div class="tab-content" id="tab-llm">
            <div class="card">
                <button id="generate-llm-btn">Generate LLM Report</button>
                <div id="llm-output" class="llm-box">No report yet</div>
                <button id="save-llm-btn" disabled>Save as PDF</button>
            </div>
        </div>

        <!-- CHAT -->
        <div class="tab-content" id="tab-chat">
            <div class="chat-container card">

                <div id="chat-log" class="chat-log"></div>

                <form id="llm-chat-form" class="chat-input">
                    <input
                        type="text"
                        id="llm-chat-input"
                        placeholder="Ask a follow-up question..."
                    />
                    <button type="submit">Send</button>
                </form>

            </div>
        </div>

    </section>

</main>

<script type="module" src="../app.js"></script>
</body>
</html>

