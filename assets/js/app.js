document.addEventListener('DOMContentLoaded', () => {
    const dueInput = document.querySelector('[data-auto-priority="due-date"]');
    const prioritySelect = document.querySelector('[data-auto-priority="select"]');
    const autoCheckbox = document.getElementById('auto_priority');

    const suggestPriority = (dateStr) => {
        if (!dateStr) return 'medium';
        const today = new Date();
        const due = new Date(dateStr);
        const diff = Math.ceil((due - today) / (1000 * 60 * 60 * 24));
        if (isNaN(diff) || diff > 7) return 'low';
        if (diff <= 2) return 'high';
        return 'medium';
    };

    const applySuggestion = () => {
        if (!dueInput || !prioritySelect || (autoCheckbox && !autoCheckbox.checked)) return;
        prioritySelect.value = suggestPriority(dueInput.value);
    };

    if (dueInput) {
        dueInput.addEventListener('change', applySuggestion);
    }
    if (autoCheckbox) {
        autoCheckbox.addEventListener('change', applySuggestion);
    }

    document.querySelectorAll('[data-chart]').forEach(canvas => {
        if (typeof Chart === 'undefined') return;
        const dataset = JSON.parse(canvas.getAttribute('data-chart-dataset'));
        const type = canvas.getAttribute('data-chart') || 'doughnut';
        const colors = dataset.colors || ['#198754', '#0d6efd', '#dc3545', '#ffc107'];
        const chartDataset = {
            data: dataset.values,
            backgroundColor: type === 'line' ? colors[0] : colors,
            borderColor: colors[0],
            fill: type !== 'line',
            tension: 0.3,
            pointBackgroundColor: colors[0],
            label: dataset.label || 'Dữ liệu'
        };
        const config = {
            type,
            data: {
                labels: dataset.labels,
                datasets: [chartDataset]
            },
            options: { responsive: true, maintainAspectRatio: false }
        };
        new Chart(canvas, config);
    });

    document.querySelectorAll('.flash-auto').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 2000);
    });
});
