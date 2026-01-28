
/* --------------------------------------------------------------
    Listener: Save Button for the LLM Report 
    Author: Evan
-------------------------------------------------------------- */

export async function createLLMSession() {
    const resp = await fetch('v2/api/session/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        
    });

    return resp.text();
}

/**
 * May need to have a body argument for the Session Identifier
 */
export async function getLLMSession(sessionName) {
    const payload = {
        session_name: sessionName
    }
    const resp = await fetch('v2/api/session', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return resp.text();
}


export async function endLLMSession() {
    const resp = await fetch('v2/api/session/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        
    });

    return resp.text();
}

export async function deleteLLMSession() {
    const resp = await fetch('v2/api/session/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        
    });

    return resp.text();
}



/* --------------------------------------------------------------
    Listener: Create LLM Report 
    Author: Evan
-------------------------------------------------------------- */
export async function generateLLMReport(input, type, relation_type, rows) {
    const payload = {
        query: rows,
        input: input,
        type: type,
        relation_type: relation_type,       

    };

    const resp = await fetch('v2/api/llm/report', {
        method: 'POST',
        body: JSON.stringify(payload)
    });

    return resp.text();
}






export async function sendLLMChat(userQuery) {
    const payload = new URLSearchParams({
        query: JSON.stringify({ user_query: userQuery }),
        query_type: 'user_chat',
        reset: 'false'
    });

    const resp = await fetch('v2/api/llm/chat', {
        method: 'POST',
        body: payload
    });

    return resp.text();
}



// export async function submitLLMFeedback(responseId, rating, isHelpful, feedbackText) {
//     const payload = {
//         response_id: responseId,
//         rating,
//         is_helpful: isHelpful,
//         feedback_text: feedbackText
//     };

//     const resp = await fetch('/api/llm/feedback', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify(payload)
//     });

//     return resp.json();
// }

export async function getMostRecentReport() {
    const resp = await fetch('v2/api/llm/report/get', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
    });

    return resp.text();
}

export async function retrieveGDInteractions(input, search_type, relation_type) {
    let gene, drug;
    if (search_type === 'gene') {
        gene = input;
        drug = "";
    } else if (search_type === 'drug') {
        gene = "";
        drug = input;
    } else {
        throw Error("Search Type is neither drug nor gene" + search_type);
    }


    const payload = {
        gene: gene,
        drug: drug, 
        relation_type: relation_type
    };

    const resp = await fetch('v2/api/query', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return resp.text();
}
