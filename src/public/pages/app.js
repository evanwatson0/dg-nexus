import { generateLLMReport, retrieveGDInteractions, sendLLMChat } from '../rest.js';
import { normaliseToRows, renderMiniGraph, renderTable } from './assets/js/graph_visualisation.js';

import { mdToPdfSelectable } from './assets/js/pdf_convert.js';

// Keep track of previous form inputs

let lastStructured = [];
let gdiSearchFormData;
let previousLLMText = "";

const generateLLMButton = document.getElementById('generate-llm-btn');
const downloadLLMButton = document.getElementById('download-llm-btn');
const saveLLMButton = document.getElementById('save-llm-btn');


/* --------------------------------------------------------------
    Listener: Gene, Drug, Interaction Submission Form
    Author: Evan
-------------------------------------------------------------- */
const gdiSearchForm = document.getElementById('gdi-search-form');

gdiSearchForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const gdiStatus = document.getElementById('gdi-search-status');
    gdiStatus.textContent = 'Searching...';

    // retrieve them using function
    gdiSearchFormData = new FormData(gdiSearchForm);
    
    let searchRespJSON, searchRespRaw, data;
    try {
        searchRespRaw = await retrieveGDInteractions(gdiSearchFormData.get('input'), gdiSearchFormData.get('gene_or_drug'), gdiSearchFormData.get('relation_type'));
        searchRespJSON = JSON.parse(searchRespRaw);
        data = searchRespJSON["data"];
    } catch (err) {
        console.error('Error sending to llm_request.php:', err);
        console.error("RAW RESPONSE:", searchRespRaw);
        return;
    }

    
    const rows = normaliseToRows(data);


    lastStructured = rows;
    renderTable(rows);
    renderMiniGraph(rows);

    // make llm button unmutted
    generateLLMButton.disabled = false;
});



/* --------------------------------------------------------------
    Listener: Gemerate LLM Report upon Button Press 
    Author: Evan
-------------------------------------------------------------- */
// let prevReportResponseID;

generateLLMButton.addEventListener('click', async(e) => {
    e.preventDefault();
    
    const llmReportOutput = document.getElementById('llm-report-output');

    let reportRespJSON, reportRespRaw;
    try {
        reportRespRaw = await generateLLMReport(gdiSearchFormData.get('input'), gdiSearchFormData.get('gene_or_drug'), gdiSearchFormData.get('relation_type'), lastStructured);
        reportRespJSON = JSON.parse(reportRespRaw);
    } catch (err) {
        console.error('Error generating llm report:', err);
        console.error("RAW RESPONSE:", reportRespRaw);
        return;
    }

    let llmText = reportRespJSON['data'];
    // prevReportResponseID = reportRespJSON['response_id'];

    // Update previous text on frontend, and store copy 
    // if user wants to save the report
    llmReportOutput.textContent = llmText || 'No LLM Text Received';
    previousLLMText = llmText;

    // Once an LLM report is generated, only then can the User
    // SAVE the LLM report, discuss the results and give feedback 
    saveLLMButton.disabled = false;
    downloadLLMButton.disabled = false;
});


/* --------------------------------------------------------------
    Listener: Download Button for the LLM Report 
    Author: Evan
-------------------------------------------------------------- */
downloadLLMButton.addEventListener('submit', async (e) => {
    const text = previousLLMText.trim();
    await mdToPdfSelectable(text, 'output.pdf', 'Report', 'assets/images/logo.png');
});



/* --------------------------------------------------------------
    Listener: Save Button for the LLM Discussion
    Author: Evan
-------------------------------------------------------------- */
const llmChatForm = document.getElementById('llm-chat-form');
const llmResponseField = document.getElementById('llm-chat-response-section');

llmChatForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    // send that request to the chatgpt

    // get the text
    const fd = new FormData(llmChatForm);

    try {
        const llmRespRaw = await sendLLMChat(fd.get('llm-chat-input'));
        const llmRespJSON = JSON.parse(llmRespRaw);

        llmResponseField.textContent = llmRespJSON['data'] || 'No LLM text received';
    } catch (err) {
        console.error('Error generating llm discussion:', err);
        console.error("RAW RESPONSE:", llmRespRaw);
        return;
    }
});