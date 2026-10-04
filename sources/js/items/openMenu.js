// POWERED BY AI

function openEditModal(item) {
    const modal = document.getElementById('editModal');
    
    document.getElementById('edit_item_id').value = item.id || '';
    document.getElementById('edit_item_name').value = item.item_name || '';
    document.getElementById('edit_item_description').value = item.item_description || '';
    document.getElementById('edit_item_art').value = item.item_art || '';
    document.getElementById('edit_item_category').value = item.item_category || '';
    document.getElementById('edit_item_cost').value = item.item_cost || '';
    document.getElementById('edit_item_retail').value = item.item_retail || '';
    document.getElementById('edit_item_manufacturer').value = item.item_manufacturer || '';
    document.getElementById('edit_item_remain').value = item.item_remain || '';
    document.getElementById('edit_item_unit').value = item.item_unit || '';
    document.getElementById('edit_item_status').value = item.item_status || '';
    
    modal.classList.add('active');
    
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.classList.remove('active');
    
    document.body.style.overflow = '';
    
    document.getElementById('editForm').reset();
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('editModal');
    
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeEditModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeEditModal();
            }
        });
    }
});

let currentDeleteItem = null;

function openDeleteModal(item) {
    currentDeleteItem = item;
    const modal = document.getElementById('deleteModal');
    const itemInfo = document.getElementById('deleteItemInfo');
    
    itemInfo.innerHTML = `
        <p class="item-name">${escapeHtml(item.item_name || 'Без названия')}</p>
        ${item.item_art ? `<p>Артикул: <strong>${escapeHtml(item.item_art)}</strong></p>` : ''}
        ${item.item_category ? `<p>Категория: ${escapeHtml(item.item_category)}</p>` : ''}
        ${item.item_retail ? `<p>Цена: ${escapeHtml(item.item_retail)} ₽</p>` : ''}
        <p class="item-id">ID: ${escapeHtml(item.id || '—')}</p>
    `;
    
    modal.classList.add('active');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
    currentDeleteItem = null;
}

function confirmDelete() {
    if (currentDeleteItem) {
        deleteItem(currentDeleteItem.id);
        closeDeleteModal();
    }
}

function escapeHtml(text) {
    if (!text) return '—';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('deleteModal');
        if (modal.classList.contains('active')) {
            closeDeleteModal();
        }
    }
});