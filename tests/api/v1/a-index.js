const results = document.getElementById('results');

async function showValidResult(type) {
    let result;

    if (type === 'getMe') {
        result = await getMe(BASE_URL, VALID_TOKEN);
    }

    results.innerHTML +=
        `
            <div class="result">
            <h3>${type} - API valid Response:</h3>
            <pre>${JSON.stringify(result, null, 2)}</pre>
            <p>Used token: "<span class='token'>${VALID_TOKEN}</span>"</p>
            </div>
        `;
}

async function showInvalidResult(type) {
    let result;

    if (type === 'getMe') {
        result = await getMe(BASE_URL, INVALID_TOKEN);
    }

    results.innerHTML +=
        `
            <div class="result">
            <h3>${type} - API invalid Response:</h3>
            <pre>${JSON.stringify(result, null, 2)}</pre>
            <p>Used token: "<span class='token'>${INVALID_TOKEN}</span>"</p>
            </div>
        `;
}

async function showEmptyResult(type) {
    let result;

    if (type === 'getMe') {
        result = await getMe(BASE_URL, EMPTY_TOKEN);
    }

    results.innerHTML +=
        `
            <div class="result">
            <h3>${type} - API empty Response:</h3>
            <pre>${JSON.stringify(result, null, 2)}</pre>
            <p>Used token: "<span class='token'>${EMPTY_TOKEN}</span>"</p>
            </div>
        `;
}