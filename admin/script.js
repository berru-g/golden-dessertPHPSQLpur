// Fonctionnalités complètes
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('messagesTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const searchInput = document.getElementById('searchInput');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageNumbers = document.getElementById('pageNumbers');

    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = rows;
    let sortColumn = null;
    let sortDirection = 'asc';

    // Recherche instantanée
    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        filteredRows = rows.filter(row => {
            return Array.from(row.cells).some(cell =>
                cell.textContent.toLowerCase().includes(searchTerm)
            );
        });
        currentPage = 1;
        updateTable();
        updatePagination();
    });

    // Tri des colonnes
    table.querySelectorAll('th').forEach(header => {
        header.addEventListener('click', () => {
            const columnIndex = parseInt(header.getAttribute('data-column'));

            // Reset les autres en-têtes
            table.querySelectorAll('th').forEach(th => {
                if (th !== header) {
                    th.classList.remove('sorted-asc', 'sorted-desc');
                }
            });

            // Toggle la direction de tri
            if (sortColumn === columnIndex) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = columnIndex;
                sortDirection = 'asc';
            }

            header.classList.toggle('sorted-asc', sortDirection === 'asc');
            header.classList.toggle('sorted-desc', sortDirection === 'desc');

            filteredRows.sort((a, b) => {
                const aValue = a.cells[columnIndex].textContent.toLowerCase();
                const bValue = b.cells[columnIndex].textContent.toLowerCase();

                if (aValue < bValue) return sortDirection === 'asc' ? -1 : 1;
                if (aValue > bValue) return sortDirection === 'asc' ? 1 : -1;
                return 0;
            });

            updateTable();
        });
    });

    // Pagination
    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        // Boutons précédent/suivant
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;

        // Numéros de page
        pageNumbers.innerHTML = '';
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === currentPage ? 'active' : '';
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                updateTable();
                updatePagination();
            });
            pageNumbers.appendChild(pageBtn);
        }
    }

    // Mise à jour du tableau
    function updateTable() {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedRows = filteredRows.slice(start, end);

        tbody.innerHTML = '';
        paginatedRows.forEach(row => tbody.appendChild(row));
    }

    // Événements pagination
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            updateTable();
            updatePagination();
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updateTable();
            updatePagination();
        }
    });

    // Initialisation
    updatePagination();
});