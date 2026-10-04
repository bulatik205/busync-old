async function getMe(BASE_URL, API_KEY) {
    try {
        const response = await fetch(`${BASE_URL}/getMe`, {
            method: 'GET',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            }
        });

        return await response.json();
    } catch (error) {
        return error;
    }
}