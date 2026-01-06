export async function createLLMSession() {
    const resp = await fetch('/api/llm/session/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_identifier: null })
    });

    return resp.json();
}

export async function generateLLMReport(rows, sessionId) {
    const payload = new URLSearchParams({
        query: JSON.stringify(rows),
        query_type: 'report',
        session_id: sessionId
    });

    const resp = await fetch('/api/llm/report/create', {
        method: 'POST',
        body: payload
    });

    return resp.json();
}

export async function sendLLMChat(userQuery, sessionId) {
    const payload = new URLSearchParams({
        query: JSON.stringify({ user_query: userQuery }),
        query_type: 'user_chat',
        session_id: sessionId,
        reset: 'false'
    });

    const resp = await fetch('/api/llm/chat', {
        method: 'POST',
        body: payload
    });

    return resp.json();
}

export async function submitLLMFeedback(responseId, rating, isHelpful, feedbackText) {
    const payload = {
        response_id: responseId,
        rating,
        is_helpful: isHelpful,
        feedback_text: feedbackText
    };

    const resp = await fetch('/api/llm/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return resp.json();
}

export async function getMostRecentReport() {
    const resp = await fetch('/api/llm/report/get', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
}

export async function retrieveGDInteractions(gene,drug,relation_type) {
    const payload = {
        gene: g,
        rating,
        is_helpful: isHelpful,
        feedback_text: feedbackText
    };

    const resp = await fetch('/api/llm/feedback', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    return resp.json();
}
