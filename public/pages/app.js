import { generateLLMReport, retrieveGDInteractions, sendLLMChat } from '../rest.js';
import { mdToPdfSelectable } from './helpers/pdf_convert.js';



let lastStructured = [];
let gdiSearchFormData;
let previousLLMText = "";

const generateLLMButton = document.addEventListener('generate-llm-btn');
const saveLLMButton = document.addEventListener('save-llm-btn');


/* --------------------------------------------------------------
    Listener: Gene, Drug, Interaction Submission Form
    Author: Evan
-------------------------------------------------------------- */
const gdiSearchForm = document.getElementById('gdi-search-form');

gdiSearchForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const gdiStatus = document.getElementById('status');
    gdiStatus.textContent = 'Searching...';

    // retrieve them using function
    gdiSearchFormData = new FormData(gdiSearchForm);
    
    try {
        const searchRespRaw = retrieveGDInteractions(gdiSearchFormData.get('input'), gdiSearchForm.get('gene_or_drug'), gdiSearchFormData.get('relation_type'));
        const searchRespJSON = JSON.parse(searchRespRaw);
    } catch (err) {
        console.error('Error sending to llm_request.php:', err);
        console.error("RAW RESPONSE:", llmRespRaw);
        return;
    }

    data = searchRespJSON["data"];
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
let prevReportResponseID;
generateLLMButton.addEventListener('submit', async(e) => {
    
    const llmReportOutput = document.getElementById('llm-report-output');

    try {
        const reportRespRaw = generateLLMReport(gdiSearchFormData.get('input'), gdiSearchForm.get('gene_or_drug'), gdiSearchFormData.get('relation_type'), lastStructured, sessionID);
        const reportRespJSON = JSON.parse(reportRespRaw);
    } catch (err) {
        console.error('Error generating llm report:', err);
        console.error("RAW RESPONSE:", llmRespRaw);
        return;
    }



    let llmText = reportRespJSON['data'];
    prevReportResponseID = reportRespJSON['response_id'];

    // Update previous text on frontend, and store copy 
    // if user wants to save the report
    llmReportOutput.textContent = llmText || 'No LLM Text Received';
    previousLLMText = llmText;


    // Once an LLM report is generated, only then can the User
    // SAVE the LLM report, discuss the results and give feedback 
    saveLLMButton.disabled = false;
});


/* --------------------------------------------------------------
    Listener: Save Button for the LLM Report 
    Author: Evan
-------------------------------------------------------------- */
saveLLMButton.addEventListener('submit', async (e) => {
    const text = previousLLMText.trim();
    await mdToPdfSelectable(text, 'output.pdf', 'Report', 'styling/logo,png');
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
        const llmRespRaw = sendLLMChat(fd.get('llm-chat-input'));
        const llmRespJSON = JSON.parse(llmRespRaw);

        llmResponseField.textContent = llmRespJSON['data'] || 'No LLM text received';
    } catch (err) {
        console.error('Error generating llm discussion:', err);
        console.error("RAW RESPONSE:", llmRespRaw);
        return;
    }
});