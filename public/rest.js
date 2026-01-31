/* --------------------------------------------------------------
    Listener: Save Button for the LLM Report 
    Author: Evan
-------------------------------------------------------------- */

export async function createLLMSession() {
    const resp = await fetch('/index.php?endpoint=session_create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    });

    return await resp.text();
}

export async function getLLMSession(session_name) {
    const payload = {
        session_name
    };

    const resp = await fetch('./index.php?endpoint=session_get', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return await resp.text();
}

export async function endLLMSession() {
    const resp = await fetch('/index.php?endpoint=session_update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    });

    return await resp.text();
}

export async function deleteLLMSession() {
    const resp = await fetch('/index.php?endpoint=session_delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    });

    return await resp.text();
}

/* --------------------------------------------------------------
    Listener: Create LLM Report 
    Author: Evan
-------------------------------------------------------------- */

export async function generateLLMReport(input, type, relation_type, rows) {
    const payload = {
        query: rows,
        input,
        type,
        relation_type
    };

    const resp = await fetch('/index.php?endpoint=llm_report_create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return await resp.text();
}

export async function sendLLMChat(userQuery) {
    const payload = {
        user_query: userQuery,
        query_type: 'user_chat',
        reset: false
    };

    const resp = await fetch('/index.php?endpoint=llm_chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return await resp.text();
}

export async function getMostRecentReport() {
    const resp = await fetch('../index.php?endpoint=llm_report_get', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    });

    return await resp.text();
}

export async function retrieveGDInteractions(input, search_type, relation_type) {
    let gene = null;
    let drug = null;

    if (search_type === 'gene') {
        gene = input;
    } else if (search_type === 'drug') {
        drug = input;
    } else {
        throw new Error("Search Type is neither drug nor gene: " + search_type);
    }

    const payload = {
        gene,
        drug,
        relation_type
    };

    const resp = await fetch('../index.php?endpoint=query', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return await resp.text();
}
