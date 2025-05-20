document.addEventListener('DOMContentLoaded', function() {
    // Aplicar tema ao carregar
    const tema = localStorage.getItem('tema') || 'claro';
    if (tema === 'escuro') {
        document.body.classList.add('dark-mode');
    }
});