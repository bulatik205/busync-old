async function copy(TOKEN) {
    try {
        const copyButton = document.getElementById(TOKEN);
        await navigator.clipboard.writeText(TOKEN);

        copyButton.innerHTML = 'Скопировано!';

        setTimeout(() => {
            copyButton.innerHTML = 'Скопировать';
        }, 2000);
    } catch (err) {
        console.error('Ошибка копирования:', err);
    }
}