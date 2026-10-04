const successProfieBlock = document.getElementById("profile-success");
const errorProfieBlock = document.getElementById("profile-error");

async function saveProfile(API_KEY) {
    const firstName = document.getElementById("first_name").value ?? "-";
    const lastName = document.getElementById("last_name").value ?? "-";
    const fatherName = document.getElementById("father_name").value ?? "-";
    const selected = document.querySelector('input[name="business_type"]:checked');
    const businessType = selected?.value ?? "-";
    const businessSite = document.getElementById("business_site").value ?? "-";
    const phone = document.getElementById("phone").value ?? "-";

    try {
        const response = await fetch(`http://localhost/busync/api/v1/saveProfile/`, {
            method: 'POST',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                "fields": {
                    "first_name": firstName,
                    "last_name": lastName,
                    "father_name": fatherName,
                    "business_type": businessType,
                    "business_site": businessSite,
                    "phone": phone
                }
            })
        });

        const result = await response.json();

        if (result.success) {
            successProfieBlock.style.display = "block";

            setTimeout(() => {
                successProfieBlock.style.display = "none";
            }, 2000);
        } else {
            errorProfieBlock.style.display = "block";

            setTimeout(() => {
                errorProfieBlock.style.display = "none";
            }, 2000);
            console.log(result.error.message)
        }
    } catch (error) {
        errorProfieBlock.style.display = "block";

        setTimeout(() => {
            errorProfieBlock.style.display = "none";
        }, 2000);
        console.log(result.error.message)
    }
}

async function createApi(API_KEY) {
    const apiKeysBlock = document.getElementById("api-keys");
    try {
        const response = await fetch(`http://localhost/busync/api/v1/createApi/`, {
            method: 'POST',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            const newRow = document.createElement('tr');
            
            const apiKey = result.fields.last_inserted_apikey;
            const apiKeySubstr = result.fields.last_inserted_apikey_substr;
            
            newRow.style.backgroundColor = '#5dce8c4e'; 
            newRow.innerHTML = `
                <td>${apiKeySubstr}...</td>
                <td>❌</td>
                <td></td>
                <td>${new Date().toLocaleDateString('ru-RU')}</td>
                <td>
                    <button onclick="copy('${apiKey}')">
                        Копировать
                    </button>
                </td>
            `;
            
            const createBlock = document.getElementById('create-api-block');
            apiKeysBlock.insertBefore(newRow, createBlock);
            
            successProfieBlock.style.display = "block";
            setTimeout(() => {
                successProfieBlock.style.display = "none";
            }, 2000);
            
            newRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
        } else {
            errorProfieBlock.style.display = "block";
            setTimeout(() => {
                errorProfieBlock.style.display = "none";
            }, 2000);
            console.log(result.error.message);
        }
    } catch (error) {
        errorProfieBlock.style.display = "block";
        setTimeout(() => {
            errorProfieBlock.style.display = "none";
        }, 2000);
        console.log(error.message); 
    }
}