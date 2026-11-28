document.addEventListener('DOMContentLoaded', () => {
    const favicon = document.createElement('link');

    favicon.rel = 'icon';
    favicon.type = 'image/png';
    favicon.href = '/favicon.png';

    document.head.appendChild(favicon);
});
