// POWERED BY AI

const itemsSuccess = document.getElementById("items-success");
const itemsError = document.getElementById("items-error");
const tableBody = document.querySelector("tbody");

function validateRequiredFields() {
    const requiredFields = [
        { id: 'item_name', name: 'Название товара' },
        { id: 'item_cost', name: 'Себестоимость' },
        { id: 'item_retail', name: 'Розничная цена' },
        { id: 'item_remain', name: 'Остаток' }
    ];

    const missingFields = [];

    for (const field of requiredFields) {
        const element = document.getElementById(field.id);
        const value = element ? element.value : null;
        
        if (!value || value.trim() === '') {
            missingFields.push(field.name);
            element.style.borderColor = 'red';
        } else {
            element.style.borderColor = '';
        }
    }

    return missingFields;
}

function resetFieldHighlight() {
    const fields = ['item_name', 'item_cost', 'item_retail', 'item_remain'];
    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.style.borderColor = '';
        }
    });
}

function addValidationListeners() {
    const fields = ['item_name', 'item_cost', 'item_retail', 'item_remain'];
    fields.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
            element.addEventListener('focus', () => {
                element.style.borderColor = '';
            });
        }
    });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '—';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addRowToTable(itemData) {
    const firstRow = tableBody.querySelector('tr');
    if (firstRow) {
        const emptyMessage = firstRow.querySelector('td[colspan="10"]');
        if (emptyMessage) {
            tableBody.innerHTML = '';
        }
    }

    const newRow = createItemRow(itemData);
    tableBody.insertBefore(newRow, tableBody.firstChild);
    
    setTimeout(() => {
        newRow.classList.add('highlight');
        setTimeout(() => {
            newRow.classList.remove('highlight');
        }, 5000);
    }, 100);
}

function createItemRow(itemData) {
    const row = document.createElement('tr');
    row.setAttribute('ondblclick', 'this.classList.toggle("highlight")');
    row.style.cursor = 'pointer';
    
    const editButton = document.createElement('button');
    editButton.className = 'edit-btn btn';
    editButton.setAttribute('onclick', `openEditModal(${JSON.stringify(itemData).replace(/'/g, "\\'")})`);
    editButton.setAttribute('title', 'Редактировать товар');
    editButton.innerHTML = '<i class="fas fa-pencil-alt"></i>';
    
    const deleteButton = document.createElement('button');
    deleteButton.className = 'delete-btn btn';
    deleteButton.setAttribute('onclick', `openDeleteModal(${JSON.stringify(itemData).replace(/'/g, "\\'")})`);
    deleteButton.setAttribute('title', 'Удалить товар');
    deleteButton.innerHTML = '<i class="fas fa-trash-can"></i>';
    
    const editCell = document.createElement('td');
    editCell.style.textAlign = 'center';
    editCell.appendChild(editButton);
    
    const deleteCell = document.createElement('td');
    deleteCell.style.textAlign = 'center';
    deleteCell.appendChild(deleteButton);
    
    row.appendChild(editCell);
    row.appendChild(deleteCell);
    row.innerHTML += `
        <td>${escapeHtml(itemData.id ?? '—')}</td>
        <td>${escapeHtml(itemData.item_name ?? '—')}</td>
        <td>${escapeHtml(itemData.item_art ?? '—')}</td>
        <td>${escapeHtml(itemData.item_category ?? '—')}</td>
        <td>${escapeHtml(itemData.item_remain ?? '—')}</td>
        <td>${escapeHtml(itemData.item_retail ?? '—')}</td>
        <td>${escapeHtml(itemData.item_cost ?? '—')}</td>
        <td>${escapeHtml(itemData.item_manufacturer ?? '—')}</td>
        <td>${escapeHtml(itemData.item_unit ?? '—')}</td>
        <td>${escapeHtml(itemData.item_status ?? '—')}</td>
        <td>${escapeHtml(itemData.item_description ?? '—')}</td>
    `;
    
    return row;
}

function appendItemsToTable(items) {
    items.forEach(item => {
        const row = createItemRow(item);
        tableBody.appendChild(row);
    });
}

function clearForm() {
    document.getElementById("item_name").value = '';
    document.getElementById("item_description").value = '';
    document.getElementById("item_art").value = '';
    document.getElementById("item_category").value = '';
    document.getElementById("item_cost").value = '';
    document.getElementById("item_retail").value = '';
    document.getElementById("item_manufacturer").value = '';
    document.getElementById("item_remain").value = '';
    document.getElementById("item_unit").value = '';
    document.getElementById("item_status").value = '';
}

async function loadMoreItems() {
    if (isLoading || !hasMoreItems) return;
    
    isLoading = true;
    
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    
    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
    if (loadingSpinner) loadingSpinner.style.display = 'inline-block';
    
    try {
        const url = `http://localhost/busync/api/v1/getItems/?limit=${ITEMS_LIMIT}&offset=${currentOffset}`;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success && result.fields && result.fields.length > 0) {
            appendItemsToTable(result.fields);
            currentOffset += result.fields.length;
            
            if (result.fields.length < ITEMS_LIMIT) {
                hasMoreItems = false;
                if (loadMoreContainer) loadMoreContainer.style.display = 'none';
                document.getElementById('noMoreItems').style.display = 'block';
            } else {
                if (loadMoreBtn) loadMoreBtn.style.display = 'inline-block';
            }
        } else {
            hasMoreItems = false;
            if (loadMoreContainer) loadMoreContainer.style.display = 'none';
            document.getElementById('noMoreItems').style.display = 'block';
        }
        
    } catch (error) {
        console.error('Ошибка при загрузке товаров:', error);
        showMessage('Ошибка при загрузке товаров: ' + error.message, 'error');
        
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) loadMoreBtn.style.display = 'inline-block';
    } finally {
        const loadingSpinner = document.getElementById('loadingSpinner');
        if (loadingSpinner) loadingSpinner.style.display = 'none';
        isLoading = false;
    }
}

async function createItem() {
    const missingFields = validateRequiredFields();
    
    if (missingFields.length > 0) {
        showMessage(`Заполните обязательные поля: ${missingFields.join(', ')}`, 'error');
        return;
    }

    const item_name = document.getElementById("item_name").value;
    const item_description = document.getElementById("item_description").value;
    const item_art = document.getElementById("item_art").value;
    const item_category = document.getElementById("item_category").value;
    const item_cost = document.getElementById("item_cost").value;
    const item_retail = document.getElementById("item_retail").value;
    const item_manufacturer = document.getElementById("item_manufacturer").value;
    const item_remain = document.getElementById("item_remain").value;
    const item_unit = document.getElementById("item_unit").value;
    const item_status = document.getElementById("item_status").value;

    if (isNaN(item_cost) || isNaN(item_retail) || isNaN(item_remain)) {
        showMessage("Себестоимость, розничная цена и остаток должны быть числами", 'error');
        return;
    }

    try {
        const response = await fetch(`http://localhost/busync/api/v1/createItem/`, {
            method: 'POST',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                "fields": {
                    item_name, item_description, item_art, item_category,
                    item_cost, item_retail, item_manufacturer,
                    item_remain, item_unit, item_status
                }
            })
        });

        const result = await response.json();

        if (result.success) {
            resetFieldHighlight();
            showMessage("Товар успешно создан!", 'success');

            addRowToTable({
                id: result.data?.id || 'Новый',
                item_name, item_art, item_category,
                item_remain, item_retail, item_cost,
                item_manufacturer, item_unit, item_status,
                item_description
            });

            clearForm();
            
            setTimeout(() => {
                location.reload();
            }, 1500);

        } else {
            let error = "Неизвестная ошибка";

            switch (result.error?.message) {
                case "Unauthorized": error = "Неавторизован"; break;
                case "Invalid API-key": error = "Неверный API-key"; break;
                case "Unauthorized session": error = "Авторизуйте сессию"; break;
                case "Server error": error = "Ошибка сервера. Попробуйте позже"; break;
                case "invalid_user_id": error = "Неверный user_id"; break;
                case "Invalid JSON format": error = "Ошибка сервера. Попробуйте позже"; break;
                case "Empty fields": error = "Ошибка сервера. Попробуйте позже"; break;
                case "Insert error": error = "Ошибка сервера. Попробуйте позже"; break;
                default: error = "Поля имеют запрещенные символы или не соответствуют требованиям";
            } 

            showMessage(error, 'error');
            console.log(result.error?.message);
        }
    } catch (error) {
        showMessage("Ошибка сервера. Попробуйте позже", 'error');
        console.log(error.message);
    }
}

async function updateItem() {
    const itemId = document.getElementById('edit_item_id').value;
    
    const itemData = {
        item_name: document.getElementById('edit_item_name').value,
        item_description: document.getElementById('edit_item_description').value,
        item_art: document.getElementById('edit_item_art').value,
        item_category: document.getElementById('edit_item_category').value,
        item_cost: document.getElementById('edit_item_cost').value,
        item_retail: document.getElementById('edit_item_retail').value,
        item_manufacturer: document.getElementById('edit_item_manufacturer').value,
        item_remain: document.getElementById('edit_item_remain').value,
        item_unit: document.getElementById('edit_item_unit').value,
        item_status: document.getElementById('edit_item_status').value
    };

    try {
        const response = await fetch(`http://localhost/busync/api/v1/editItem/?id=${itemId}`, {
            method: 'POST',
            headers: {
                'API-key': API_KEY,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ fields: itemData })
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Товар успешно обновлен!', 'success');
            closeEditModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showMessage('Ошибка: ' + (result.error?.message || 'Неизвестная ошибка'), 'error');
            
            if (result.error?.code === 404) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        }
    } catch (error) {
        showMessage('Ошибка соединения с сервером', 'error');
        console.error('Error:', error);
    }
}

async function deleteItem(itemId) {
    showMessage('Удаление товара...', 'info');
    
    try {
        const response = await fetch(`http://localhost/busync/api/v1/deleteItem/?id=${itemId}`, {
            method: 'DELETE',
            headers: {
                'API-key': API_KEY
            }
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.error?.message || `HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();

        if (result.success === true) {
            showMessage('Товар успешно удален!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            const errorMessage = result.error?.message || 'Неизвестная ошибка';
            showMessage(`Ошибка удаления: ${errorMessage}`, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Ошибка соединения. Попробуйте позже', 'error');
    }
}

function showMessage(text, type = 'error') {
    if (!itemsSuccess || !itemsError) return;
    
    itemsSuccess.style.display = 'none';
    itemsError.style.display = 'none';
    
    if (type === 'success') {
        itemsSuccess.style.display = 'block';
        itemsSuccess.innerHTML = `<p>✅ ${text}</p>`;
        
        setTimeout(() => {
            itemsSuccess.style.display = 'none';
        }, 3000);
    } else if (type === 'error') {
        itemsError.style.display = 'block';
        itemsError.innerHTML = `<p>❌ ${text}</p>`;
        
        setTimeout(() => {
            itemsError.style.display = 'none';
        }, 5000);
    } else if (type === 'info') {
        itemsError.style.display = 'block';
        itemsError.style.backgroundColor = '#3498db'; 
        itemsError.innerHTML = `<p>ℹ️ ${text}</p>`;
        
        setTimeout(() => {
            itemsError.style.display = 'none';
            itemsError.style.backgroundColor = ''; 
        }, 2000);
    }
}

function initInfiniteScroll() {
    window.addEventListener('scroll', () => {
        if (!hasMoreItems || isLoading) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const documentHeight = document.documentElement.scrollHeight;
        
        if (scrollPosition >= documentHeight - 200) {
            loadMoreItems();
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    addValidationListeners();
    
    if (typeof currentOffset === 'undefined') {
        currentOffset = ITEMS_LIMIT || 50;
    }
    
    const tableRows = tableBody ? tableBody.children.length : 0;
    if (tableRows < ITEMS_LIMIT) {
        hasMoreItems = false;
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        if (loadMoreContainer) {
            loadMoreContainer.style.display = 'none';
        }
        document.getElementById('noMoreItems').style.display = 'block';
    }
    
    initInfiniteScroll();
});