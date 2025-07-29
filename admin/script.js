document.addEventListener('DOMContentLoaded', function () {
    // Création de la modale (MODIFIÉ)
    const modal = document.createElement('div');
    modal.className = 'message-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Message complet</h3>
            <div class="modal-meta">
                <div class="modal-id">ID: <span id="message-id"></span></div>
                <div class="modal-email">Expéditeur: <span id="message-email"></span></div>
            </div>
            <div class="modal-message"></div>
            <button class="reply-btn">Répondre</button>
        </div>
    `;
    document.body.appendChild(modal);

    // Gestion des clics sur les lignes (MODIFIÉ)
    document.querySelectorAll('.message-row').forEach(row => {
        row.addEventListener('click', function (e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) return;

            const messageId = this.getAttribute('data-id');
            const isRead = this.getAttribute('data-read') === '1';
            const fullMessage = this.getAttribute('data-fullmessage');
            const senderEmail = this.getAttribute('email'); // Nouveau: récupère l'email

            // Afficher le message + metadata (MODIFIÉ)
            document.querySelector('.modal-message').innerHTML = nl2br(fullMessage);
            document.getElementById('message-id').textContent = messageId;
            document.getElementById('message-email').textContent = senderEmail;
            modal.style.display = 'flex';

            if (!isRead) {
                markAsRead(messageId, this);
            }
        });
    });

    // Gestion du bouton Répondre (NOUVEAU)
    modal.querySelector('.reply-btn').addEventListener('click', function () {
        const email = document.getElementById('message-email').textContent;
        window.location.href = `mailto:${email}?subject=Re: Votre message`;
    });
    // Dans la partie tri des colonnes, vérifiez que les index correspondent :
    document.querySelectorAll('th').forEach((header, index) => {
        header.addEventListener('click', () => {
            sortTable(index); // L'index doit correspondre à la bonne colonne
        });
    });

    // Fonction pour convertir les retours à la ligne
    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    // Fermeture de la modale
    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// markasread
function markAsRead(messageId, rowElement) {
    fetch('mark_as_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + messageId
    })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch {
                    throw new Error('Invalid JSON: ' + text);
                }
            });
        })
        .then(data => {
            console.log("Réponse du serveur:", data); // Debug

            if (data.success) {
                // 1. Mise à jour visuelle
                rowElement.classList.remove('unread');
                rowElement.style.backgroundColor = 'white';

                // 2. Mise à jour compteur
                const unreadCountElement = document.getElementById('unreadCount');
                unreadCountElement.textContent = data.unread;

                // 3. Cache badge si 0
                document.getElementById('unreadBadge').style.display =
                    data.unread > 0 ? 'flex' : 'none';
            } else {
                console.error('Erreur serveur:', data.error);
            }
        })
        .catch(error => {
            console.error('Erreur fetch:', error);
        });
}



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

// exportation de la bdd en json
document.getElementById('exportJsonBtn').addEventListener('click', async () => {
    try {
        // 1. Afficher un loader
        const btn = document.getElementById('exportJsonBtn');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
        btn.disabled = true;

        // 2. Récupérer les données
        const response = await fetch('export_to_json.php');
        const data = await response.json();
        
        // 3. Créer et déclencher le téléchargement
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = `export_${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        
        // 4. Nettoyage
        setTimeout(() => {
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            // Remettre le bouton normal
            btn.innerHTML = '<i class="fas fa-database"></i> Exporter en JSON';
            btn.disabled = false;
        }, 100);

    } catch (error) {
        console.error('Erreur export:', error);
        alert('Erreur lors de l\'export');
    }
});