async function saveProfile(API_KEY) {
    try {
        const response = await fetch(`http://localhost/busync/api/v1/saveProfile/index.php`, {
            method: 'POST',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                "fields": {
                    "first_name": "Hello",
                    "last_name": "Hello",
                    "business_type": "HelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloHelloH",  // <-------- this return error 422 -- long
                    "father_name": "", // <-------- this return error 422 -- empty 
                    "phone": "H" // <-------- this return error 422 -- short
                }
            })
        });

        console.log(await response.json());
    } catch (error) {
        console.log(error);
    }
}

saveProfile("123hash");