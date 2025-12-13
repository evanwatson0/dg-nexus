import { mdToPdfSelectable } from '../llm/pdf_convert.js';

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
const form = document.getElementById('search-form');
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




/**
 * Author: Claire
 * 
 * 把後端或 DGIdb 的回傳，轉成統一列格式
 * 期望欄位：drug, gene, relation, pmid, source
 * 
 */
function normaliseToRows(payload) {
    // 1) 若已是標準列陣列
    if (Array.isArray(payload) && payload.length && payload[0].drug && payload[0].gene) {
        return payload;
    }

    if (Array.isArray(payload) && payload.length && payload[0].GeneSymbol !== undefined) {
        return payload.map(row => ({
            gene: row.GeneLongName || row.GeneSymbol || "",
            drug: row.DrugName || "",
            relation: (row.RelationType || "").toLowerCase(),
            source: row.Citations  || "",
            notes: row.Notes
        }));
    }
}


/**
 * Author: Claire 
 * 
 * @param {*} rows 
 * @returns 
 */
function renderTable(rows) {
    if (rows === undefined) {
        tableEl.innerHTML = '<div class="muted">No data</div>';
        return;   
    }
    if (!rows.length) {
        tableEl.innerHTML = '<div class="muted">No data</div>';
        return;
    }
    const head = `
    <table>
        <thead>
        <tr>
            <th>Drug</th><th>Gene</th><th>Relation</th><th>Source</th><th>Notes</th>
        </tr>
        </thead>
        <tbody>
    `;
    const body = rows.map(r => `
    <tr>
        <td>${esc(r.drug)}</td>
        <td>${esc(r.gene)}</td>
        <td>${esc(r.relation || '')}</td>
        <td>${esc(r.source || '')}</td>
        <td>${esc(r.notes || '')}</td>
    </tr>
    `).join('');
    const foot = '</tbody></table>';
    tableEl.innerHTML = head + body + foot;
}


/**
 * Author: Claire 
 * 
 * 簡易 SVG 網路圖：把 gene 放左邊，drug 放右邊，中間拉線
 */
function renderMiniGraph(rows) {
    if (rows === undefined) {
        return;   
    }

    while (svg.firstChild) svg.removeChild(svg.firstChild);
    // 新增：記錄節點位置與所有線的引用
    const nodePositions = {}; // nodeName -> {x,y}
    const lineRefs = [];      // {lineEl, gene, drug}

    if (!rows.length) {
        const t = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        t.setAttribute('x', 20); t.setAttribute('y', 30);
        t.textContent = 'No data to visualize';
        svg.appendChild(t);
        return;
    }

    const genes = [...new Set(rows.map(r => r.gene).filter(Boolean))];
    const drugs = [...new Set(rows.map(r => r.drug).filter(Boolean))];

    const W = svg.clientWidth || 800;
    const H = svg.clientHeight || 360;
    const leftX = 120, rightX = W - 120;
    const padTop = 40, padBottom = 40;
    const gy = d3scalePositions(genes.length, H, padTop, padBottom);
    const dy = d3scalePositions(drugs.length, H, padTop, padBottom);

    // 線
    rows.forEach(r => {
        const gi = genes.indexOf(r.gene);
        const di = drugs.indexOf(r.drug);
        if (gi < 0 || di < 0) return;

        const line = document.createElementNS('http://www.w3.org/2000/svg','line');
        line.setAttribute('x1', leftX); line.setAttribute('y1', gy[gi]);
        line.setAttribute('x2', rightX); line.setAttribute('y2', dy[di]);
        line.setAttribute('stroke', '#cbd5e1'); line.setAttribute('stroke-width', '1.5');
        svg.appendChild(line);

        // ⭐⭐ 新增：把這條線存起來
        lineRefs.push({
            lineEl: line,
            gene: r.gene,
            drug: r.drug
        });

        // 在中點放標籤
        const mx = (leftX + rightX)/2, my = (gy[gi] + dy[di]) / 2;
        const label = document.createElementNS('http://www.w3.org/2000/svg','text');
        label.setAttribute('x', mx); label.setAttribute('y', my - 4);
        label.setAttribute('text-anchor', 'middle');
        label.setAttribute('font-size', '11');
        label.setAttribute('fill', '#64748b');
        label.textContent = (r.relation || '').toLowerCase();
        svg.appendChild(label);
        line._label = label; //Tie the label to the line
    });

    // 基因節點
    genes.forEach((g, i) => nodeCircle(leftX, gy[i], g, '#10b981', lineRefs, nodePositions));    
    // 藥物節點
    drugs.forEach((d, i) => nodeCircle(rightX, dy[i], d, '#3b82f6', lineRefs, nodePositions));

    function nodeCircle(x, y, name, color, lineRefs, nodePositions) {
        nodePositions[name] = { x, y };

        const circle = document.createElementNS('http://www.w3.org/2000/svg','circle');
        circle.setAttribute('cx', x);
        circle.setAttribute('cy', y);
        circle.setAttribute('r', 18);
        circle.setAttribute('fill', '#fff');
        circle.setAttribute('stroke', color);
        circle.setAttribute('stroke-width','2');
        circle.style.cursor = 'grab';
        svg.appendChild(circle);

        const text = document.createElementNS('http://www.w3.org/2000/svg','text');
        text.setAttribute('x', x);
        text.setAttribute('y', y + 4);
        text.setAttribute('text-anchor','middle');
        text.setAttribute('font-size','11');
        text.textContent = name;
        svg.appendChild(text);

        let dragging = false, ox = 0, oy = 0;

        circle.addEventListener('mousedown', e => {
            dragging = true;
            circle.style.cursor = 'grabbing';
            ox = e.clientX - x;
            oy = e.clientY - y;
        });

        document.addEventListener('mouseup', () => {
            dragging = false;
            circle.style.cursor = 'grab';
        });

        document.addEventListener('mousemove', e => {
            if (!dragging) return;

            const nx = e.clientX - ox;
            const ny = e.clientY - oy;
            x = nx;
            y = ny;

            // 更新節點位置
            nodePositions[name].x = nx;
            nodePositions[name].y = ny;

            // 移動圓和文字
            circle.setAttribute('cx', nx);
            circle.setAttribute('cy', ny);
            text.setAttribute('x', nx);
            text.setAttribute('y', ny + 4);

            // ⭐⭐ 讓所有連到這個 node 的線一起動
            lineRefs.forEach(ref => {
                const line = ref.lineEl;

                // 更新線的位置
                if (ref.gene === name) {
                    line.setAttribute('x1', nx);
                    line.setAttribute('y1', ny);
                }
                if (ref.drug === name) {
                    line.setAttribute('x2', nx);
                    line.setAttribute('y2', ny);
                }

                // ⭐⭐ 更新線的 label（中點）
                const label = line._label;
                if (label) {
                    const x1 = +line.getAttribute('x1');
                    const y1 = +line.getAttribute('y1');
                    const x2 = +line.getAttribute('x2');
                    const y2 = +line.getAttribute('y2');

                    const mx = (x1 + x2) / 2;
                    const my = (y1 + y2) / 2;

                    label.setAttribute('x', mx);
                    label.setAttribute('y', my - 4);
                }
            });

        });
    }


    function d3scalePositions(n, height, top, bottom) {
    if (n <= 1) return [height / 2];
        const usable = height - top - bottom;
        const step = usable / (n - 1);
        return Array.from({length:n}, (_, i) => top + i * step);
    }
}

/**
 * Author: Claire
 * 
 * Reformats fields taken from database for use in HTML output
 */
function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}