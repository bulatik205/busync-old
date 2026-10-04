function clearQueryString() {
    window.history.replaceState({}, document.title, window.location.pathname);
}

document.addEventListener('DOMContentLoaded', () => {
    clearQueryString();
});