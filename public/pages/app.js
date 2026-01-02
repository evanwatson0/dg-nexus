import { mdToPdfSelectable } from './elements/pdf_convert.js';

// const saveBtn = document.getElementById('save-btn');
const saveLlmBtn = document.getElementById('save-llm-btn');
const svg = document.getElementById('graph');

// LLM chat features
const llmChatForm = document.getElementById('llm-chat-form');
const llmChatBtn = document.getElementById('llm-chat-button');
const llmChatInput = document.getElementById('llm-chat-input');
const llmQueryField = document.getElementById('llm-chat-response-section');

// Feedback Button
const feedbackButton = document.getElementById('feedback-submit');


// Contains the real updated LLM Text
let previousLLMText = "";

// Automatically generate new session upon a new Search of the database
let currentSessionID = null;
let currentReportResponseId = null;


// 前端暫存最近一次的結構化結果，給 Save to DB 用
let lastStructured = [];



/* --------------------------------------------------------------
    Listener: Gene, Drug, Interaction Submission Form
    Author: Claire
-------------------------------------------------------------- */
const form = document.getElementById('gene-drug-search-form');
const statusEl = document.getElementById('status');
const tableEl = document.getElementById('table');
const llmEl = document.getElementById('llm');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    statusEl.textContent = 'Searching...';
    const fd = new FormData(form);
    
    // Retrieve data from SQL Database
    // 送往後端檔案：interaction_retrieval.php
    // 後端建議接收：$_POST['gene'], $_POST['drug'], $_POST['relation_type']
    let interactionsRaw;
    let data;
    try {
        const resp = await fetch('../data_flow/interaction_retrieval.php', { 
            method: 'POST', 
            body: fd 
        });

        // Get the Raw Text
        interactionsRaw = await resp.text();

        // attempt to Parse the JSON
        const ret = JSON.parse(interactionsRaw);
        data = ret["data"]
    } catch (err) {
        console.error("JSON parse error:", err);
        console.error("RAW RESPONSE:", interactionsRaw);
        statusEl.textContent = "Response is not valid JSON. See console for details.";
        return;
    }

    // 嘗試把任意形狀的資料轉成表格列
    const rows = normaliseToRows(data);
    lastStructured = rows;
    renderTable(rows);
    renderMiniGraph(rows);


    // Continue Input Only If User Wants LLM report
    if (fd.get('want_llm') !== 'yes') {
        llmEl.textContent = 'Not Selected';
        statusEl.textContent = 'Done';

        saveLlmBtn.disabled = true;
        return;   
    }


    // CREATE NEW USER SESSION
    let identifierRaw;
    try {
        // const fd2 = new FormData();
        // fd2.append("action", "create_session");
        // fd2.append("user_identifier", null);

        const payload = {
            action: "create_session",
            user_identifier: null
        }

        const resp2 = await fetch("../llm/llm_chat_storage.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });

        identifierRaw = await resp2.text();
        const identifierJSON = JSON.parse(identifierRaw);

        currentSessionID = identifierJSON['id'];

    } catch (err) {
        console.error('Error creating user session:', err);
        console.error('RAW RESPONSE:', identifierRaw);
        return;
    }


    // SEND DATA TO LLM (llm_request.php)
    // 若選擇要 LLM 報告，額外呼叫 llm_request.php
    let llmText = null;
    let llmRespRaw;
    try {

        const llmPayload = new URLSearchParams({
            gene: fd.get('gene') || '',
            drug: fd.get('drug') || '',
            relation_type: fd.get('relation_type') || '',
            // append all interactions as arguments for the LLM also
            query: JSON.stringify(rows),
            query_type: 'report',
            session_id: currentSessionID
        });
        const llmResp = await fetch('../llm/llm_request.php', { method: 'POST', body: llmPayload });
        
        const llmRespRaw = await llmResp.text(); // 也可能是 JSON，看你們後端決定
        const llmRespJSON = JSON.parse(llmRespRaw);
        llmText = llmRespJSON['data'];
        currentReportResponseId = llmRespJSON['response_id'];
    } catch (err) {
        console.error('Error sending to llm_request.php:', err);
        console.error("RAW RESPONSE:", llmRespRaw);
        return;
    }

    // Update previous text on frontend, and store copy 
    // if user wants to save the report
    llmEl.textContent = llmText || 'No LLM text received';
    previousLLMText = llmText;

    // Once an LLM report is generated, only then can the User
    // SAVE the LLM report, discuss the results and give feedback 
    saveLlmBtn.disabled = !llmText; 
    llmChatBtn.disabled = !llmText;
    feedbackButton.disabled = !llmText;

    statusEl.textContent = 'Done';

});



/* --------------------------------------------------------------
    Listener: Save Button for the LLM Report 
    Author: Evan
-------------------------------------------------------------- */
saveLlmBtn.addEventListener('click', async () => {

    const text = previousLLMText.trim();
    await mdToPdfSelectable(text, 'output.pdf', 'Report', 'styling/logo.png');

});


/* --------------------------------------------------------------
    Listener: Discuss w/ AI Asssistant Button 
    Author: Evan
-------------------------------------------------------------- */

llmChatForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    // send that request to the chatgpt

    // get the text
    const fd = new FormData(llmChatForm);

    let llmText;

    try {
        const llmPayload = new URLSearchParams({
            query: JSON.stringify({user_query: fd.get('llm-chat-input')}),
            query_type: 'user_chat',
            session_id: currentSessionID,
            reset: 'false'
        });

        const llmResp = await fetch('../llm/llm_request.php', { method: 'POST', body: llmPayload });
        const llmResRaw = await llmResp.text(); // 也可能是 JSON，看你們後端決定
        const llmResJson = JSON.parse(llmResRaw);

        llmQueryField.textContent = llmResJson['data'] || 'No LLM text received';

    } catch (err) {
        console.error("Error:", err);
        console.error("RAW RESPONSE:", llmText);
        llmQueryField.textContent = "Response is not valid JSON. See console for details.";
    }


});


/* --------------------------------------------------------------
    Listener: User Feedback Form Submission
    Authors: Claire, Evan
-------------------------------------------------------------- */

const feedbackForm = document.getElementById('user-feedback');
const feedbackStatus = document.getElementById('feedback-status');
feedbackForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    let fd = new FormData(feedbackForm);
    try {
        const payload = {
            action: "add_feedback",
            response_id: currentReportResponseId,
            rating: fd.get('feedback-rating'),
            is_helpful: fd.get('feedback-rating'),
            feedback_text: fd.get('feedback-comment')
        }

        const resp2 = await fetch("../llm/llm_chat_storage.php", {
            method: "POST",
            body: JSON.stringify(payload)
        });
        feedbackStatus.textContent = "Uploaded Feedback!"
    } catch (err) {
        console.log('error feedback from', err);
        return;
    }


});



